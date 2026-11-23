<section class="product__details-section product-reviews" id="reviews" aria-labelledby="reviews-title">
    <h2 class="product__details-section-title" id="reviews-title">Отзывы</h2>

    @if (session('review_status'))
        <p role="status">{{ session('review_status') }}</p>
    @endif
    @if ($errors->any())
        <ul role="alert">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @auth
        @if ($myReview)
            <p>Твой отзыв · {{ App\Models\Review::STATUS_LABELS[$myReview->status] }}</p>
        @elseif (! auth()->user()->is_blocked)
            <form class="product-reviews__form" method="POST" action="{{ route('reviews.store', $product->slug) }}">
                @csrf
                <label for="review-rating">Оценка</label>
                <select id="review-rating" name="rating" required>
                    <option value="">Выбери от 1 до 5</option>
                    @for ($score = 5; $score >= 1; $score--)
                        <option value="{{ $score }}" @selected(old('rating') == $score)>{{ $score }} из 5</option>
                    @endfor
                </select>
                <label for="review-comment">Твой отзыв</label>
                <textarea id="review-comment" name="comment" rows="4" maxlength="2000" required>{{ is_string(old('comment')) ? old('comment') : '' }}</textarea>
                <button class="product__action-btn product__action-btn--primary" type="submit">Отправить отзыв</button>
            </form>
        @endif
    @else
        <p><a href="{{ route('login') }}">Войди в аккаунт</a>, чтобы оставить отзыв.</p>
    @endauth

    @forelse ($reviews as $review)
        <article class="product-reviews__item">
            <p><strong>{{ $review->user->first_name }} {{ mb_substr($review->user->last_name, 0, 1) }}.</strong>
                · {{ $review->rating }} из 5</p>
            <p class="product__description">{{ $review->comment }}</p>
        </article>
    @empty
        <p>Опубликованных отзывов пока нет.</p>
    @endforelse

    <x-catalog-pagination :items="$reviews" />
</section>
