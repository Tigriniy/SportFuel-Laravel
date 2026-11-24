<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Оформление заказа — SportFuel</title>
  <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/cart.css') }}">
</head>
<body>
<main class="checkout">
  <a class="checkout__back" href="{{ route('cart.index') }}">← Вернуться в корзину</a>
  <h1>Оформление заказа</h1>

  @if ($errors->any())
    <ul class="cart__notice cart__notice--error" role="alert">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  @endif

  <div class="checkout__grid">
    <form class="checkout__panel checkout__form" method="POST" action="{{ route('checkout.store') }}">
      @csrf
      <input type="hidden" name="quote" value="{{ $quote }}">

      <label for="delivery_method">Способ доставки</label>
      <select name="delivery_method" id="delivery_method" required>
        @foreach ($deliveryOptions as $method => $option)
          <option value="{{ $method }}" data-fee="{{ $option['fee'] }}" @selected(old('delivery_method', 'courier') === $method)>
            {{ $option['label'] }} · {{ $option['fee'] ? number_format($option['fee'] / 100, 0, ',', ' ').' ₽' : 'бесплатно' }}
          </option>
        @endforeach
      </select>

      <label for="delivery_address">Адрес доставки или пункта выдачи</label>
      <input type="text" name="delivery_address" id="delivery_address" maxlength="255"
             autocomplete="street-address" value="{{ is_string(old('delivery_address')) ? old('delivery_address') : $user->delivery_address }}" required>

      <label for="contact_phone">Телефон (10 или 11 цифр)</label>
      <input type="tel" name="contact_phone" id="contact_phone" maxlength="11" pattern="[0-9]{10,11}"
             autocomplete="tel" inputmode="numeric" value="{{ is_string(old('contact_phone')) ? old('contact_phone') : $user->phone }}" required>

      <label for="payment_method">Способ оплаты</label>
      <select name="payment_method" id="payment_method" required>
        @foreach (\App\Models\Order::PAYMENT as $method => $label)
          <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $label }}</option>
        @endforeach
      </select>

      <label for="comment">Комментарий к заказу</label>
      <textarea name="comment" id="comment" rows="3" maxlength="500">{{ is_string(old('comment')) ? old('comment') : '' }}</textarea>
      <button type="submit" class="cart__empty-button">Подтвердить заказ</button>
    </form>

    <section class="checkout__panel" aria-labelledby="checkout-items">
      <h2 id="checkout-items">Товары в заказе</h2>
      <ul class="checkout__items">
        @foreach ($items as $item)
          <li>
            <span>{{ $item->product->name }}@if ($item->variant) · {{ $item->variant->title }}@endif × {{ $item->quantity }}</span>
            <strong>{{ number_format($item->subtotalInKopecks() / 100, 2, ',', ' ') }} ₽</strong>
          </li>
        @endforeach
      </ul>
      @php($selectedDelivery = old('delivery_method', 'courier'))
      @php($selectedFee = $deliveryOptions[is_string($selectedDelivery) ? $selectedDelivery : 'courier']['fee'] ?? $deliveryOptions['courier']['fee'])
      <p>Товары: {{ number_format($subtotal / 100, 2, ',', ' ') }} ₽</p>
      <p>Доставка: <span id="checkout-fee">{{ number_format($selectedFee / 100, 2, ',', ' ') }} ₽</span></p>
      <p class="checkout__sum" aria-live="polite">К оплате при получении:
        <strong id="checkout-total" data-subtotal="{{ $subtotal }}">{{ number_format(($subtotal + $selectedFee) / 100, 2, ',', ' ') }} ₽</strong>
      </p>
    </section>
  </div>
</main>
<script src="{{ asset('assets/js/checkout.js') }}" defer></script>
</body>
</html>
