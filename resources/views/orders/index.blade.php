@extends('orders.layout')

@section('title', 'Мои заказы')

@section('content')
  <a class="checkout__back" href="{{ route('profile') }}">← В личный кабинет</a>
  <h1>Мои заказы</h1>

  @forelse ($orders as $order)
    <article class="cart__line">
      <div class="cart__line-info">
        <h2><a href="{{ route('orders.show', $order) }}">Заказ №{{ $order->id }}</a></h2>
        @if ($order->created_at)
          <time datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('d.m.Y H:i') }}</time>
        @endif
        <p>Позиций: {{ $order->items_count }}</p>
      </div>
      <span>{{ $order->statusLabel() }}</span>
      <strong>{{ number_format($order->total_amount, 2, ',', ' ') }} ₽</strong>
    </article>
  @empty
    <div class="checkout__panel">
      <p>У тебя пока нет заказов.</p>
      <a class="checkout__back" href="{{ route('catalog') }}">Перейти в каталог</a>
    </div>
  @endforelse

  <x-catalog-pagination :items="$orders" />
@endsection
