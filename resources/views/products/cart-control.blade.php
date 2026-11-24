@php($canAdd = auth()->check() && ! auth()->user()->is_blocked && $stock > 0)

@if ($errors->cart->any())
    <ul class="product__cart-errors" role="alert">
        @foreach ($errors->cart->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('cart.store', $product->slug) }}" class="product__cart-form">
    @csrf

    @if ($product->variants->isNotEmpty())
        <fieldset id="packaging" class="product__variations product__cart-options">
            <legend class="product__variations-title">Варианты упаковки</legend>
            <ul class="product__variations-options">
                @foreach ($product->variants as $variant)
                    <li class="product__variations-option product__variant-detail">
                        <label class="product__cart-option">
                            @if ($canAdd)
                                <input type="radio" name="variant_id" value="{{ $variant->id }}"
                                       @disabled($variant->stock < 1)
                                       @checked($variant->stock > 0 && (string) old('variant_id', $product->variants->firstWhere('stock', '>', 0)?->id) === (string) $variant->id)
                                       required>
                            @endif
                            <span class="product__variations-option-quantity">{{ $variant->title }}</span>
                            <span class="product__variations-option-price">{{ number_format($variant->price, 2, ',', ' ') }} ₽</span>
                            <small>{{ $variant->stock > 0 ? 'В наличии' : 'Нет в наличии' }}</small>
                        </label>
                    </li>
                @endforeach
            </ul>
        </fieldset>
    @endif

    @if ($canAdd)
        <div class="product__cart-actions">
            <label>Количество
                <input type="number" name="quantity" min="1" max="{{ min($stock, App\Models\CartItem::MAX_QUANTITY) }}"
                       value="{{ is_numeric(old('quantity')) ? old('quantity') : 1 }}" required>
            </label>
            <button class="product__action-btn product__action-btn--primary" type="submit">Добавить в корзину</button>
        </div>
    @elseif (auth()->guest() && $stock > 0)
        <a class="product__cart-login" href="{{ route('login') }}">Войди, чтобы добавить в корзину</a>
    @endif
</form>
