@extends('admin.layout')
@section('title', 'Товары')

@section('content')
    <a class="button" href="{{ route('admin.products.create') }}">Добавить товар</a>
    @if ($products->isEmpty())
        <p>В каталоге пока нет товаров.</p>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th scope="col">Название</th><th scope="col">Категория</th><th scope="col">Бренд</th><th scope="col">Цена</th><th scope="col">Остаток</th><th scope="col">В магазине</th><th scope="col">Действия</th></tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name }}</td>
                            <td>{{ $product->brand->name }}</td>
                            <td>{{ number_format($product->price, 2, ',', ' ') }} ₽</td>
                            <td>{{ $product->stock }}</td>
                            <td>{{ $product->is_active ? 'Виден' : 'Скрыт' }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.products.edit', $product) }}">Изменить</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Удалить этот товар?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button-text" type="submit">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-catalog-pagination :items="$products" />
    @endif
@endsection
