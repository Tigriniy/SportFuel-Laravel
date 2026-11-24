<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        abort_if($user->is_blocked, 403);

        $items = $this->loadItems($user);

        if ($problem = $this->cartProblem($items)) {
            return $this->backToCart($problem);
        }

        $subtotal = $items->sum(fn (CartItem $item): int => $item->subtotalInKopecks());
        $deliveryOptions = [];
        foreach (Order::DELIVERY as $key => $option) {
            $deliveryOptions[$key] = ['label' => $option['label'], 'fee' => Order::deliveryFee($key, $subtotal)];
        }

        return view('checkout', [
            'user' => $user,
            'items' => $items,
            'subtotal' => $subtotal,
            'deliveryOptions' => $deliveryOptions,
            'quote' => $this->quote($items),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->is_blocked, 403);

        $data = $request->validate([
            'delivery_method' => ['required', 'string', Rule::in(array_keys(Order::DELIVERY))],
            'payment_method' => ['required', 'string', Rule::in(array_keys(Order::PAYMENT))],
            'delivery_address' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'regex:/\A[0-9]{10,11}\z/'],
            'comment' => ['nullable', 'string', 'max:500'],
            'quote' => ['required', 'string', 'size:64'],
        ], [
            'delivery_method.*' => 'Выбери способ доставки.',
            'payment_method.*' => 'Выбери способ оплаты.',
            'delivery_address.required' => 'Укажи адрес доставки или пункта выдачи.',
            'delivery_address.max' => 'Адрес должен быть не длиннее 255 символов.',
            'contact_phone.*' => 'Введи номер из 10 или 11 цифр.',
            'comment.max' => 'Комментарий должен быть не длиннее 500 символов.',
            'quote.*' => 'Заново открой страницу оформления заказа.',
        ]);

        return DB::transaction(function () use ($request, $data): RedirectResponse {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            abort_if($user->is_blocked, 403);

            // Cart changes take the same user lock. Product locks also serialize stock changes
            // with other buyers and with catalog edits.
            $productIds = $user->cartItems()->lockForUpdate()->pluck('product_id')->unique();
            Product::whereKey($productIds->all())->orderBy('id')->lockForUpdate()->get();
            $items = $this->loadItems($user);

            if ($problem = $this->cartProblem($items)) {
                return $this->backToCart($problem);
            }

            if (! hash_equals($this->quote($items), $data['quote'])) {
                return $this->backToCart('Состав корзины или цены изменились. Проверь итог и начни оформление заново.');
            }

            $subtotal = $items->sum(fn (CartItem $item): int => $item->subtotalInKopecks());
            $fee = Order::deliveryFee($data['delivery_method'], $subtotal);
            $order = $user->orders()->create([
                'status' => Order::PENDING,
                'delivery_method' => $data['delivery_method'],
                'payment_method' => $data['payment_method'],
                'delivery_address' => $data['delivery_address'],
                'contact_phone' => $data['contact_phone'],
                'comment' => $data['comment'] ?? null,
                'delivery_fee' => $this->toDecimal($fee),
                'total_amount' => $this->toDecimal($subtotal + $fee),
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product->name,
                    'variant_title' => $item->variant?->title,
                    'unit_price' => $item->unitPrice(),
                    'quantity' => $item->quantity,
                    'total' => $this->toDecimal($item->subtotalInKopecks()),
                ]);
                ($item->variant ?? $item->product)->decrement('stock', $item->quantity);
            }
            $user->cartItems()->delete();

            return redirect()->route('cart.index')
                ->with('cart_status', "Заказ №{$order->id} оформлен. Сумма к оплате при получении: "
                    .number_format($order->total_amount, 2, ',', ' ').' ₽.');
        }, 3);
    }

    private function loadItems(User $user): Collection
    {
        return $user->cartItems()
            ->with(['product' => fn ($query) => $query->withExists('variants'), 'variant'])
            ->orderBy('id')->get();
    }

    private function quote(Collection $items): string
    {
        return hash('sha256', json_encode($items->map(fn (CartItem $item): array => [
            $item->id, $item->quantity, $item->unitPrice(),
        ])->all(), JSON_THROW_ON_ERROR));
    }

    private function cartProblem(Collection $items): ?string
    {
        if ($items->isEmpty()) {
            return 'Корзина пуста. Добавь товары, чтобы оформить заказ.';
        }

        foreach ($items as $item) {
            if ($item->quantity > $item->availableQuantity()) {
                return 'Некоторые позиции недоступны в выбранном количестве. Проверь корзину.';
            }
        }

        return null;
    }

    private function backToCart(string $message): RedirectResponse
    {
        return redirect()->route('cart.index')->withErrors(['checkout' => $message], 'cart');
    }

    private function toDecimal(int $kopecks): string
    {
        return sprintf('%d.%02d', intdiv($kopecks, 100), $kopecks % 100);
    }
}
