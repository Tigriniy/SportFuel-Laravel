<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CatalogController extends Controller
{
    public function home(): View
    {
        return view('index', [
            'products' => Product::with(['brand', 'cover'])->where('is_active', true)->latest('id')->limit(4)->get(),
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'brand', 'category', 'variants',
            'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id'),
            'relatedProducts' => fn ($query) => $query->where('products.is_active', true)->with(['brand', 'cover'])->limit(8),
        ]);

        $published = $product->reviews()->where('status', Review::APPROVED);
        $rating = (clone $published)->selectRaw('COUNT(*) as total, AVG(rating) as average')->first();
        $myReview = $request->user()
            ? $product->reviews()->where('user_id', $request->user()->id)->first()
            : null;

        return view('carta-tovara', [
            'product' => $product,
            'reviews' => $published->with('user')->latest('id')->paginate(10, pageName: 'reviews_page')->fragment('reviews'),
            'rating' => $rating,
            'myReview' => $myReview,
        ]);
    }

    public function index(Request $request): View|RedirectResponse
    {
        $sortOptions = [
            'newest' => 'Сначала новые',
            'price_asc' => 'Сначала дешевле',
            'price_desc' => 'Сначала дороже',
            'name' => 'По названию',
        ];

        $validator = Validator::make($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'array', 'list', 'max:100'],
            'category.*' => ['required', 'integer', 'min:1'],
            'brand' => ['nullable', 'array', 'list', 'max:100'],
            'brand.*' => ['required', 'integer', 'min:1'],
            'min_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'max_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'sort' => ['nullable', 'string', 'in:'.implode(',', array_keys($sortOptions))],
        ], [
            'q.*' => 'Введи поисковый запрос длиной до 100 символов.',
            'category.*' => 'Выбери категории из списка.',
            'category.*.*' => 'Выбери категории из списка.',
            'brand.*' => 'Выбери бренды из списка.',
            'brand.*.*' => 'Выбери бренды из списка.',
            'min_price.*' => 'Введи корректную минимальную цену в рублях.',
            'max_price.*' => 'Введи корректную максимальную цену в рублях.',
            'sort.*' => 'Выбери сортировку из списка.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('catalog')->withErrors($validator);
        }

        $filters = array_filter($validator->validated(), fn ($value) => $value !== null);
        $min = $filters['min_price'] ?? null;
        $max = $filters['max_price'] ?? null;

        if ($min !== null && $max !== null && (float) $min > (float) $max) {
            unset($filters['min_price'], $filters['max_price']);

            return redirect()->route('catalog', $filters)
                ->withErrors(['min_price' => 'Цена «от» должна быть меньше или равна цене «до».']);
        }

        $products = Product::with(['brand', 'cover'])->where('is_active', true);

        if (isset($filters['q'])) {
            $term = strtr($filters['q'], ['!' => '!!', '%' => '!%', '_' => '!_']);
            $products->whereRaw("name LIKE ? ESCAPE '!'", ['%'.$term.'%']);
        }

        if (! empty($filters['category'])) {
            $products->whereIn('category_id', $filters['category']);
        }

        if (! empty($filters['brand'])) {
            $products->whereIn('brand_id', $filters['brand']);
        }

        if ($min !== null) {
            $products->where('price', '>=', $min);
        }

        if ($max !== null) {
            $products->where('price', '<=', $max);
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $products->orderBy('price'),
            'price_desc' => $products->orderByDesc('price'),
            'name' => $products->orderBy('name'),
            'newest' => $products,
        };

        return view('katalog', [
            'products' => $products->orderByDesc('id')->paginate(16)->appends($filters),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
            'sortOptions' => $sortOptions,
        ]);
    }
}
