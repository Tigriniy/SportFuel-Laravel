<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class CatalogController extends Controller
{
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
