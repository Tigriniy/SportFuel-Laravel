@props(['product', 'saved' => null])

<article class="product-card">
    <div class="product-card__inner">
        <div class="product-card__media">
            <a href="{{ route('products.show', $product->slug) }}" class="product-card__media-link" aria-label="{{ $product->name }}">
                @if ($product->cover)
                    <img src="{{ asset($product->cover->path) }}" alt="{{ $product->cover->alt ?? $product->name }}" class="product-card__image" loading="lazy">
                @else
                    <span class="product-card__placeholder">Фото пока нет</span>
                @endif
            </a>
            <x-favorite-toggle :product="$product" :saved="$saved ?? ($product->is_favorite ?? false)" class="favorite-toggle--tile" />
        </div>
        <a href="{{ route('products.show', $product->slug) }}" class="product-card__link">
            <p class="product-card__name">{{ $product->name }}</p>
            <p class="product-card__brand">{{ $product->brand->name }}</p>
        </a>
        @php
            $variants = $product->variants;
            $variant = $variants->count() === 1 ? $variants->first() : null;
            $inStock = ($variants->isEmpty() ? $product->stock : $variants->max('stock')) > 0;
        @endphp
        <div class="product-card__bottom">
            <div>
                <span class="product-card__price">{{ number_format($variant?->price ?? $product->price, 2, ',', ' ') }} руб.</span>
                <p class="product-card__availability">{{ $inStock ? 'В наличии' : 'Нет в наличии' }}</p>
            </div>
            @if ($inStock)
                @if ($variants->count() > 1)
                    <a class="product-card__quick-cart" href="{{ route('products.show', $product->slug) }}#packaging"
                       aria-label="Выбрать упаковку: {{ $product->name }}" title="Выбрать упаковку">
                        <img src="{{ asset('assets/images/systemImg/add_cart.svg') }}" alt="">
                    </a>
                @elseif (auth()->guest())
                    <a class="product-card__quick-cart" href="{{ route('login') }}"
                       aria-label="Войди, чтобы добавить в корзину: {{ $product->name }}" title="Войди, чтобы добавить в корзину">
                        <img src="{{ asset('assets/images/systemImg/add_cart.svg') }}" alt="">
                    </a>
                @elseif (! auth()->user()->is_blocked)
                    <form method="POST" action="{{ route('cart.store', $product->slug) }}">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        @if ($variant)
                            <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                        @endif
                        <button type="submit" class="product-card__quick-cart"
                                aria-label="В корзину: {{ $product->name }}" title="В корзину">
                            <img src="{{ asset('assets/images/systemImg/add_cart.svg') }}" alt="">
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</article>
