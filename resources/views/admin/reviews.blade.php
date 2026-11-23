@extends('admin.layout')
@section('title', 'Модерация отзывов')

@section('content')
    @forelse ($reviews as $review)
        <article class="editor">
            <h2>{{ $review->product->name }}</h2>
            <p>{{ $review->user->login }} · {{ $review->rating }} из 5 · {{ App\Models\Review::STATUS_LABELS[$review->status] }}</p>
            <p class="review-comment">{{ $review->comment }}</p>
            <form class="actions" method="POST" action="{{ route('admin.reviews.update', $review) }}">
                @csrf
                @method('PATCH')
                @if ($review->status !== App\Models\Review::APPROVED)
                    <button class="button" name="status" value="approved" type="submit">Опубликовать</button>
                @endif
                @if ($review->status !== App\Models\Review::REJECTED)
                    <button class="button-text" name="status" value="rejected" type="submit">Отклонить</button>
                @endif
            </form>
        </article>
    @empty
        <p>Отзывов пока нет.</p>
    @endforelse

    <x-catalog-pagination :items="$reviews" />
@endsection
