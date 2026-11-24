<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $items = $request->user()?->cartItems()
            ->with(['product' => fn ($query) => $query->with('cover')->withExists('variants'), 'variant'])
            ->oldest('id')->get() ?? collect();

        $total = $items->sum(fn (CartItem $item): int => $item->availableQuantity() >= $item->quantity ? $item->subtotalInKopecks() : 0
        );

        return view('cart', ['items' => $items, 'total' => $total]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_if($request->user()->is_blocked, 403);

        $data = $request->validateWithBag('cart', [
            'variant_id' => ['nullable', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1', 'max:'.CartItem::MAX_QUANTITY],
        ], $this->quantityMessages());

        DB::transaction(function () use ($request, $product, $data): void {
            User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            $variantId = $data['variant_id'] ?? null;
            $key = $variantId === null ? 'product:'.$product->id : 'variant:'.$variantId;
            $cart = $request->user()->cartItems();
            $item = $cart->where('item_key', $key)->first();
            $quantity = ($item?->quantity ?? 0) + (int) $data['quantity'];
            $this->checkStock($product, $variantId, $quantity);

            if ($item) {
                $item->update(['quantity' => $quantity]);
            } else {
                $request->user()->cartItems()->create([
                    'product_id' => $product->id,
                    'variant_id' => $variantId,
                    'item_key' => $key,
                    'quantity' => $quantity,
                ]);
            }
        }, 3);

        return redirect()->route('cart.index')->with('cart_status', 'Товар добавлен в корзину.');
    }

    public function update(Request $request, int $item): RedirectResponse
    {
        abort_if($request->user()->is_blocked, 403);
        $productId = $request->user()->cartItems()->findOrFail($item)->product_id;
        $data = $request->validateWithBag('cart', [
            'quantity' => ['required', 'integer', 'min:1', 'max:'.CartItem::MAX_QUANTITY],
        ], $this->quantityMessages());

        DB::transaction(function () use ($request, $item, $productId, $data): void {
            User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $product = Product::query()->lockForUpdate()->findOrFail($productId);
            $entry = $request->user()->cartItems()->findOrFail($item);

            $this->checkStock($product, $entry->variant_id, (int) $data['quantity']);
            $entry->update(['quantity' => $data['quantity']]);
        }, 3);

        return redirect()->route('cart.index')->with('cart_status', 'Количество обновлено.');
    }

    public function destroy(Request $request, int $item): RedirectResponse
    {
        abort_if($request->user()->is_blocked, 403);
        $productId = $request->user()->cartItems()->findOrFail($item)->product_id;

        DB::transaction(function () use ($request, $item, $productId): void {
            User::query()->lockForUpdate()->findOrFail($request->user()->id);
            Product::query()->lockForUpdate()->findOrFail($productId);
            $request->user()->cartItems()->findOrFail($item)->delete();
        }, 3);

        return redirect()->route('cart.index')->with('cart_status', 'Товар удалён из корзины.');
    }

    private function checkStock(Product $product, int|string|null $variantId, int $quantity): void
    {
        if (! $product->is_active) {
            throw ValidationException::withMessages(['quantity' => 'Товар больше недоступен.'])->errorBag('cart');
        }

        $stock = $product->stock;

        if ($variantId !== null) {
            $variant = $product->variants()->find($variantId);
            if (! $variant) {
                throw ValidationException::withMessages(['variant_id' => 'Выбери доступный вариант этого товара.'])->errorBag('cart');
            }
            $stock = $variant->stock;
        } elseif ($product->variants()->exists()) {
            throw ValidationException::withMessages(['variant_id' => 'Выбери вариант упаковки.'])->errorBag('cart');
        }

        $limit = min($stock, CartItem::MAX_QUANTITY);

        if ($quantity > $limit) {
            throw ValidationException::withMessages([
                'quantity' => $limit > 0 ? "Можно добавить не больше {$limit} шт. этой позиции." : 'Товара нет в наличии.',
            ])->errorBag('cart');
        }
    }

    private function quantityMessages(): array
    {
        return ['quantity.*' => 'Укажи целое количество от 1 до '.CartItem::MAX_QUANTITY.'.'];
    }
}
