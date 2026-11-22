<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::with(['category', 'brand'])->orderByDesc('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Product(['is_active' => true]));
    }

    public function edit(Product $product): View
    {
        return $this->form($product->load(['images', 'variants', 'relatedProducts']));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->saveProduct($request, new Product);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        return $this->saveProduct($request, $product);
    }

    public function destroy(Product $product): RedirectResponse
    {
        $paths = DB::transaction(function () use ($product) {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $paths = $locked->images()->pluck('path');
            $locked->delete();

            return $paths;
        });

        foreach ($paths as $path) {
            $this->deleteUploadedFile($path);
        }

        return redirect()->route('admin.products.index')->with('status', 'Товар удалён.');
    }

    private function form(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'relatedOptions' => Product::query()->when($product->exists, fn ($query) => $query->whereKeyNot($product->id))
                ->orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function saveProduct(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'description' => ['nullable', 'string', 'max:10000'],
            'composition' => ['nullable', 'string', 'max:10000'],
            'specs' => ['nullable', 'array', 'max:100'],
            'specs.*' => ['array:name,value'],
            'specs.*.name' => ['nullable', 'required_with:specs.*.value', 'string', 'max:120'],
            'specs.*.value' => ['nullable', 'required_with:specs.*.name', 'string', 'max:5000'],
            'variants' => ['nullable', 'array', 'max:20'],
            'variants.*' => ['array:id,title,price,stock'],
            'variants.*.id' => ['nullable', 'integer', 'distinct'],
            'variants.*.title' => ['required', 'string', 'max:120'],
            'variants.*.price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'variants.*.stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'related_ids' => ['nullable', 'array', 'max:8'],
            'related_ids.*' => ['required', 'integer', 'distinct', 'exists:products,id', Rule::notIn([$product->id])],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'is_active' => ['required', 'boolean'],
            'photos' => ['nullable', 'array', 'max:8'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_images' => ['nullable', 'array', 'max:8'],
            'remove_images.*' => ['required', 'integer', 'distinct'],
            'cover_id' => ['nullable', 'integer'],
        ], [
            'name.required' => 'Введите название товара.',
            'name.max' => 'Название должно содержать не более 255 символов.',
            'category_id.required' => 'Выберите категорию.',
            'category_id.exists' => 'Выбранная категория не найдена.',
            'brand_id.required' => 'Выберите бренд.',
            'brand_id.exists' => 'Выбранный бренд не найден.',
            'price.*' => 'Укажите цену от 0 до 99 999 999,99 руб. с точностью до копеек.',
            'stock.*' => 'Укажите остаток целым числом от 0 до 2 147 483 647.',
            'photos.*.*' => 'Загрузите фото JPG, PNG или WebP размером до 2 МБ.',
            'photos.max' => 'У товара может быть не более 8 фотографий.',
            'specs.*.name.required_with' => 'Введите название для каждой заполненной характеристики.',
            'specs.*.value.required_with' => 'Введите значение для каждой названной характеристики.',
            'variants.*.title.required' => 'Введите название каждого варианта упаковки.',
            'variants.*.price.*' => 'Укажите корректную цену для каждого варианта упаковки.',
            'variants.*.stock.*' => 'Укажите целый неотрицательный остаток для каждого варианта упаковки.',
            'related_ids.max' => 'Можно выбрать не более 8 связанных товаров.',
            'related_ids.*.*' => 'Проверьте список связанных товаров.',
            'description.max' => 'Описание слишком длинное.',
            'composition.max' => 'Состав слишком длинный.',
            'specs.max' => 'Характеристики слишком длинные.',
        ]);

        $uploads = [];

        try {
            foreach ($request->file('photos', []) ?? [] as $photo) {
                $path = $photo->store('catalog', 'public');
                if (! $path) {
                    throw ValidationException::withMessages(['photos' => 'Не удалось сохранить фотографию.']);
                }

                $uploads[] = 'storage/'.$path;
            }

            $oldPaths = DB::transaction(function () use ($product, $data, $uploads) {
                if ($product->exists) {
                    $product = Product::query()->lockForUpdate()->findOrFail($product->id);
                } else {
                    $product->slug = Str::uuid()->toString();
                }

                $product->fill(collect($data)->only([
                    'name', 'category_id', 'brand_id', 'description', 'composition', 'price', 'stock', 'is_active',
                ])->all());
                $product->specs = collect($data['specs'] ?? [])
                    ->filter(fn (array $row): bool => isset($row['name'], $row['value']))
                    ->values()->all();
                $product->save();

                $product->relatedProducts()->sync($data['related_ids'] ?? []);
                $this->saveVariants($product, $data['variants'] ?? []);

                return $this->savePhotos($product, $uploads, $data['remove_images'] ?? [], $data['cover_id'] ?? null);
            });
        } catch (Throwable $exception) {
            foreach ($uploads as $path) {
                $this->deleteUploadedFile($path);
            }

            throw $exception;
        }

        foreach ($oldPaths as $path) {
            $this->deleteUploadedFile($path);
        }

        return redirect()->route('admin.products.index')->with('status', 'Товар сохранён.');
    }

    private function saveVariants(Product $product, array $rows): void
    {
        $variants = $product->variants()->get()->keyBy('id');
        $keptIds = [];

        foreach ($rows as $row) {
            if (isset($row['id'])) {
                $variant = $variants->get($row['id']);
                if (! $variant) {
                    throw ValidationException::withMessages(['variants' => 'Обновите страницу и повторите изменения вариантов.']);
                }

                $variant->update(Arr::except($row, ['id']));
                $keptIds[] = $variant->id;
            } else {
                $keptIds[] = $product->variants()->create($row + ['sku' => Str::uuid()->toString()])->id;
            }
        }

        $product->variants()->whereNotIn('id', $keptIds)->delete();
    }

    private function savePhotos(Product $product, array $uploads, array $removeIds, int|string|null $coverId): array
    {
        $photos = $product->images()->orderBy('sort_order')->orderBy('id')->get()->toBase()->keyBy('id');
        $removed = $photos->only($removeIds);

        if ($removed->count() !== count($removeIds) || ($coverId !== null && ! $photos->has($coverId))) {
            throw ValidationException::withMessages(['photos' => 'Обновите страницу и повторите изменения фотографий.']);
        }

        $remaining = $photos->except($removeIds);
        if ($remaining->count() + count($uploads) > 8) {
            throw ValidationException::withMessages(['photos' => 'У товара может быть не более 8 фотографий.']);
        }

        $product->images()->whereKey($removeIds)->delete();
        $nextPosition = $photos->max('sort_order') ?? 0;

        foreach ($uploads as $path) {
            $image = $product->images()->create(['path' => $path, 'sort_order' => ++$nextPosition]);
            $remaining->put($image->id, $image);
        }

        $cover = $remaining->get($coverId) ?? $remaining->firstWhere('is_primary', true) ?? $remaining->first();

        if ($cover) {
            $product->images()->where('is_primary', true)->whereKeyNot($cover->id)->update(['is_primary' => false]);
            $cover->update(['is_primary' => true]);
        }

        return $removed->pluck('path')->all();
    }

    private function deleteUploadedFile(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'storage/catalog/')) {
            Storage::disk('public')->delete(Str::after($path, 'storage/'));
        }
    }
}
