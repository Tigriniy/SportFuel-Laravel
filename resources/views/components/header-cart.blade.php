@props(['mobile' => false])

@php
    $request = request();
    if (! $request->attributes->has('cart_count')) {
        $request->attributes->set('cart_count', (int) (auth()->user()?->cartItems()->sum('quantity') ?? 0));
    }
    $cartCount = $request->attributes->get('cart_count');
@endphp

<a href="{{ route('cart.index') }}" class="{{ $mobile ? 'header__mobile-icon' : 'header__icon-link' }} header__cart"
   aria-label="Корзина{{ $cartCount ? ', количество товаров: '.$cartCount : '' }}">
    <img src="{{ asset('assets/images/systemImg/cart.svg') }}" alt="">
    @if ($cartCount)
        <span class="header__cart-count" aria-hidden="true">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
    @endif
</a>
