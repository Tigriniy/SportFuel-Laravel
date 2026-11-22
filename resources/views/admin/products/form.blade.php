@extends('admin.layout')
@section('title', $product->exists ? 'Редактирование товара' : 'Новый товар')

@section('content')
    @if ($categories->isEmpty() || $brands->isEmpty())
        <p>Чтобы добавить товар, создай хотя бы одну категорию и один бренд.</p>
    @else
        <form class="editor" method="POST" enctype="multipart/form-data"
              action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
            @csrf
            @if ($product->exists)
                @method('PATCH')
            @endif

            <label for="name">Название</label>
            <input id="name" name="name" required maxlength="255" value="{{ old('name', $product->name) }}">

            <label for="category_id">Категория</label>
            <select name="category_id" id="category_id" required>
                <option value="">Выбери категорию</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>

            <label for="brand_id">Бренд</label>
            <select name="brand_id" id="brand_id" required>
                <option value="">Выбери бренд</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected((string) old('brand_id', $product->brand_id) === (string) $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>

            <label for="price">Цена, ₽</label>
            <input id="price" name="price" type="number" min="0" max="99999999.99" step="0.01" required value="{{ old('price', $product->price) }}">

            <label for="stock">Количество на складе</label>
            <input id="stock" name="stock" type="number" min="0" max="2147483647" step="1" required value="{{ old('stock', $product->stock ?? 0) }}">

            <label for="description">Описание</label>
            <textarea id="description" name="description" rows="4" maxlength="10000">{{ old('description', $product->description) }}</textarea>

            <label for="composition">Состав</label>
            <textarea id="composition" name="composition" rows="3" maxlength="10000">{{ old('composition', $product->composition) }}</textarea>

            <label for="specs">Характеристики (по одной на строку)</label>
            <textarea id="specs" name="specs" rows="3" maxlength="5000">{{ old('specs', implode("\n", $product->specs ?? [])) }}</textarea>

            @if ($product->exists && $product->cover)
                <img class="cover-preview" src="{{ asset($product->cover->path) }}" alt="{{ $product->name }}">
                <label class="check"><input type="checkbox" name="remove_cover" value="1" @checked(old('remove_cover'))> Удалить текущее изображение</label>
            @endif
            <label for="cover">Изображение (JPG, PNG или WebP, до 2 МБ)</label>
            <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp">

            <input type="hidden" name="is_active" value="0">
            <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> Показывать в каталоге магазина</label>

            <button class="button" type="submit">Сохранить товар</button>
        </form>
    @endif
    <p><a href="{{ route('admin.products.index') }}">Назад к товарам</a></p>
@endsection
