<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        return $this->form($product->load('cover'));
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
            'specs' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'is_active' => ['required', 'boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_cover' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Введите название товара.',
            'name.max' => 'Название должно содержать не более 255 символов.',
            'category_id.required' => 'Выберите категорию.',
            'category_id.exists' => 'Выбранная категория не найдена.',
            'brand_id.required' => 'Выберите бренд.',
            'brand_id.exists' => 'Выбранный бренд не найден.',
            'price.*' => 'Укажите цену от 0 до 99 999 999,99 руб. с точностью до копеек.',
            'stock.*' => 'Укажите остаток целым числом от 0 до 2 147 483 647.',
            'cover.*' => 'Загрузите изображение JPG, PNG или WebP размером до 2 МБ.',
            'description.max' => 'Описание слишком длинное.',
            'composition.max' => 'Состав слишком длинный.',
            'specs.max' => 'Характеристики слишком длинные.',
        ]);

        $uploadPath = null;
        if ($request->hasFile('cover')) {
            $uploadPath = $request->file('cover')->store('catalog', 'public');
            if (! $uploadPath) {
                throw ValidationException::withMessages(['cover' => 'Не удалось сохранить файл изображения.']);
            }
        }

        $removeCover = $request->boolean('remove_cover');
        unset($data['cover'], $data['remove_cover']);
        $data['specs'] = empty($data['specs']) ? null : preg_split('/\R/u', $data['specs'], -1, PREG_SPLIT_NO_EMPTY);

        try {
            $oldPath = DB::transaction(function () use ($product, $data, $uploadPath, $removeCover) {
                if ($product->exists) {
                    $product = Product::query()->lockForUpdate()->findOrFail($product->id);
                } else {
                    $product->slug = Str::uuid()->toString();
                }

                $product->fill($data)->save();

                if (! $uploadPath && ! $removeCover) {
                    return null;
                }

                $cover = $product->cover()->first();
                $oldPath = $cover?->path;
                $cover?->delete();

                if ($uploadPath) {
                    $product->images()->create(['path' => 'storage/'.$uploadPath, 'is_primary' => true]);
                }

                return $oldPath;
            });
        } catch (Throwable $exception) {
            if ($uploadPath) {
                Storage::disk('public')->delete($uploadPath);
            }

            throw $exception;
        }

        $this->deleteUploadedFile($oldPath);

        return redirect()->route('admin.products.index')->with('status', 'Товар сохранён.');
    }

    private function deleteUploadedFile(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'storage/catalog/')) {
            Storage::disk('public')->delete(Str::after($path, 'storage/'));
        }
    }
}
