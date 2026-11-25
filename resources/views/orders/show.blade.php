@extends('orders.layout')

@section('title', 'Заказ №'.$order->id)

@section('content')
  <a class="checkout__back" href="{{ route('orders.index') }}">← К моим заказам</a>
  <h1>Заказ №{{ $order->id }}</h1>

  <div class="checkout__grid">
    <section class="checkout__panel" aria-labelledby="order-items">
      <h2 id="order-items">Состав заказа</h2>
      <ul class="checkout__items">
        @foreach ($order->items as $item)
          <li>
            <div>
              <strong>{{ $item->product_name }}</strong>
              @if ($item->variant_title)
                <p>{{ $item->variant_title }}</p>
              @endif
              <p>{{ number_format($item->unit_price, 2, ',', ' ') }} ₽ × {{ $item->quantity }} шт.</p>
            </div>
            <strong>{{ number_format($item->total, 2, ',', ' ') }} ₽</strong>
          </li>
        @endforeach
      </ul>
      <p>Доставка: {{ number_format($order->delivery_fee, 2, ',', ' ') }} ₽</p>
      <p class="checkout__sum">Итого: {{ number_format($order->total_amount, 2, ',', ' ') }} ₽</p>
    </section>

    <section class="checkout__panel" aria-labelledby="order-details">
      <h2 id="order-details">Данные заказа</h2>
      <dl class="order-history__details">
        <dt>Статус</dt>
        <dd>{{ $order->statusLabel() }}</dd>
        @if ($order->created_at)
          <dt>Дата оформления</dt>
          <dd><time datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('d.m.Y H:i') }}</time></dd>
        @endif
        <dt>Способ доставки</dt>
        <dd>{{ \App\Models\Order::DELIVERY[$order->delivery_method]['label'] ?? $order->delivery_method }}</dd>
        <dt>Адрес</dt>
        <dd>{{ $order->delivery_address }}</dd>
        <dt>Оплата</dt>
        <dd>{{ \App\Models\Order::PAYMENT[$order->payment_method] ?? $order->payment_method }}</dd>
        <dt>Телефон</dt>
        <dd>{{ $order->contact_phone }}</dd>
        @if (filled($order->comment))
          <dt>Комментарий</dt>
          <dd>{{ $order->comment }}</dd>
        @endif
      </dl>
    </section>
  </div>
@endsection
