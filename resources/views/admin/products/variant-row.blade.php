<div class="product-editor-row product-editor-row--variant" data-row>
    @if (! empty($variant['id']))
        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant['id'] }}">
    @endif
    <label>Упаковка
        <input name="variants[{{ $index }}][title]" maxlength="120" placeholder="500 г" value="{{ $variant['title'] ?? '' }}" required>
    </label>
    <label>Цена, ₽
        <input name="variants[{{ $index }}][price]" type="number" min="0" max="99999999.99" step="0.01" value="{{ $variant['price'] ?? '' }}" required>
    </label>
    <label>Остаток
        <input name="variants[{{ $index }}][stock]" type="number" min="0" max="2147483647" step="1" value="{{ $variant['stock'] ?? 0 }}" required>
    </label>
    <button type="button" class="button-text" data-remove>Убрать</button>
</div>
