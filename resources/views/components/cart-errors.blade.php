@if ($errors->cart->any())
    <ul class="product-card__cart-errors" role="alert">
        @foreach ($errors->cart->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif
