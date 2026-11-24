<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $favorites = $request->user()?->visibleFavorites()
            ->with(['product.brand', 'product.cover', 'product.variants'])
            ->latest('id')->paginate(16);

        return view('favorite', ['favorites' => $favorites]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_if($request->user()->is_blocked, 403);
        abort_unless($product->is_active, 404);

        $request->user()->favorites()->createOrFirst(['product_id' => $product->id]);

        return back()->with('favorite_status', 'Товар добавлен в избранное.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        abort_if($request->user()->is_blocked, 403);

        $request->user()->favorites()->where('product_id', $product->id)->delete();

        return back()->with('favorite_status', 'Товар удалён из избранного.');
    }
}
