@extends('admin.layout')
@section('title', $title)

@section('content')
    <h2>{{ $item->exists ? 'Редактирование записи' : 'Новая запись' }}</h2>
    <form class="editor" method="POST"
          action="{{ $item->exists ? route('admin.references.update', [$kind, $item->id]) : route('admin.references.store', $kind) }}">
        @csrf
        @if ($item->exists)
            @method('PATCH')
        @endif
        <label for="name">Название</label>
        <input id="name" name="name" required maxlength="120" value="{{ old('name', $item->name) }}">
        <label for="description">Описание</label>
        <textarea id="description" name="description" rows="3" maxlength="2000">{{ old('description', $item->description) }}</textarea>
        <button class="button" type="submit">Сохранить</button>
        @if ($item->exists)
            <a href="{{ route('admin.references.index', $kind) }}">Отменить редактирование</a>
        @endif
    </form>

    @if ($records->isEmpty())
        <p>Записей пока нет.</p>
    @else
        <div class="table-wrap">
            <table>
                <thead><tr><th scope="col">Название</th><th scope="col">Описание</th><th scope="col">Товаров</th><th scope="col">Действия</th></tr></thead>
                <tbody>
                    @foreach ($records as $record)
                        <tr>
                            <td>{{ $record->name }}</td>
                            <td>{{ $record->description }}</td>
                            <td>{{ $record->products_count }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.references.edit', [$kind, $record->id]) }}">Изменить</a>
                                <form method="POST" action="{{ route('admin.references.destroy', [$kind, $record->id]) }}" onsubmit="return confirm('Удалить эту запись?')">
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
        <x-catalog-pagination :items="$records" />
    @endif
@endsection
