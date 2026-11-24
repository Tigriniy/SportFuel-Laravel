@props(['product', 'saved' => false])

@auth
    @unless (auth()->user()->is_blocked)
        <form {{ $attributes->class('favorite-toggle') }} method="POST"
              action="{{ route($saved ? 'favorites.destroy' : 'favorites.store', $product->slug) }}">
            @csrf
            @if ($saved)
                @method('DELETE')
            @endif
            <button type="submit" aria-label="{{ $saved ? 'Убрать из избранного' : 'Добавить в избранное' }}: {{ $product->name }}"
                    title="{{ $saved ? 'Убрать из избранного' : 'Добавить в избранное' }}">
                <img src="{{ asset('assets/images/systemImg/'.($saved ? 'Heart_favorite.svg' : 'heart_favoroite_button_for_cart.svg')) }}" alt="">
            </button>
        </form>
    @endunless
@else
    <a {{ $attributes->class('favorite-toggle') }} href="{{ route('login') }}"
       aria-label="Войди, чтобы добавить в избранное: {{ $product->name }}" title="Войди, чтобы добавить в избранное">
        <img src="{{ asset('assets/images/systemImg/heart_favoroite_button_for_cart.svg') }}" alt="">
    </a>
@endauth
