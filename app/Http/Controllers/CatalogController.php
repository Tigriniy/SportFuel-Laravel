<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class CatalogController extends Controller
{
    public function home(): View
    {
        return view('index', [
            'products' => Product::with(['brand', 'cover'])->where('is_active', true)->latest('id')->limit(4)->get(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'brand', 'category', 'variants',
            'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id'),
            'relatedProducts' => fn ($query) => $query->where('products.is_active', true)->with(['brand', 'cover'])->limit(8),
        ]);

        return view('carta-tovara', ['product' => $product]);
    }

    public function index(): View
    {
        return view('katalog', [
            'products' => Product::with(['brand', 'cover'])
                ->where('is_active', true)
                ->orderBy('id')
                ->paginate(16),
        ]);
    }
}
