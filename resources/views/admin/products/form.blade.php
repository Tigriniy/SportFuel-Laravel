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

            <fieldset class="product-editor-group" data-repeat>
                <legend>Характеристики</legend>
                <div data-rows>
                    @foreach ((array) old('specs', $product->specificationRows() ?: [[]]) as $row)
                        @include('admin.products.spec-row', ['spec' => $row, 'index' => $loop->index])
                    @endforeach
                </div>
                <template>@include('admin.products.spec-row', ['spec' => [], 'index' => '__INDEX__'])</template>
                <button class="button-text" type="button" data-add>Добавить характеристику</button>
            </fieldset>

            <fieldset class="product-editor-group" data-repeat>
                <legend>Варианты упаковки</legend>
                <div data-rows>
                    @foreach ((array) old('variants', $product->variants->toArray()) as $row)
                        @include('admin.products.variant-row', ['variant' => $row, 'index' => $loop->index])
                    @endforeach
                </div>
                <template>@include('admin.products.variant-row', ['variant' => [], 'index' => '__INDEX__'])</template>
                <button class="button-text" type="button" data-add>Добавить вариант</button>
            </fieldset>

            @if ($relatedOptions->isNotEmpty())
                <label for="related_ids">Связанные товары (до 8)</label>
                <select id="related_ids" name="related_ids[]" multiple size="5" aria-describedby="related-help">
                    @foreach ($relatedOptions as $option)
                        <option value="{{ $option->id }}" @selected(in_array($option->id, (array) old('related_ids', $product->relatedProducts->modelKeys())))>{{ $option->name }}</option>
                    @endforeach
                </select>
                <small id="related-help">Для выбора нескольких товаров удерживай Ctrl или Command.</small>
            @endif

            <fieldset class="product-editor-group">
                <legend>Фото товара</legend>
                @foreach ($product->images as $photo)
                    <div class="product-editor-photo">
                        <img class="cover-preview" src="{{ asset($photo->path) }}" alt="Фото {{ $loop->iteration }} «{{ $product->name }}»">
                        <div>
                            <label class="check">
                                <input type="radio" name="cover_id" value="{{ $photo->id }}"
                                       @checked((string) old('cover_id', $product->images->firstWhere('is_primary', true)?->id) === (string) $photo->id)>
                                Обложка
                            </label>
                            <label class="check">
                                <input type="checkbox" name="remove_images[]" value="{{ $photo->id }}"
                                       @checked(in_array($photo->id, (array) old('remove_images', [])))>
                                Удалить
                            </label>
                        </div>
                    </div>
                @endforeach
                <label for="photos">Добавить фото (JPG, PNG или WebP)</label>
                <input id="photos" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
                <small>До 8 фото на товар, до 2 МБ каждое. Первое фото становится обложкой, если она ещё не выбрана.</small>
            </fieldset>

            <input type="hidden" name="is_active" value="0">
            <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> Показывать в каталоге магазина</label>

            <button class="button" type="submit">Сохранить товар</button>
        </form>
    @endif
    <p><a href="{{ route('admin.products.index') }}">Назад к товарам</a></p>
    <script src="{{ asset('assets/js/product-editor.js') }}" defer></script>
@endsection
