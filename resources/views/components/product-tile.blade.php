@props(['product'])

<article class="product-card">
    <div class="product-card__inner">
        <a href="{{ route('products.show', $product->slug) }}" class="product-card__link">
            @if ($product->cover)
                <img src="{{ asset($product->cover->path) }}" alt="{{ $product->cover->alt ?? $product->name }}" class="product-card__image" loading="lazy">
            @else
                <span class="product-card__placeholder">Фото пока нет</span>
            @endif
            <p class="product-card__name">{{ $product->name }}</p>
            <p class="product-card__brand">{{ $product->brand->name }}</p>
        </a>
        <div class="product-card__bottom">
            <span class="product-card__price">{{ number_format($product->price, 2, ',', ' ') }} руб.</span>
            <span>{{ $product->stock > 0 ? 'В наличии' : 'Нет в наличии' }}</span>
        </div>
    </div>
</article>
