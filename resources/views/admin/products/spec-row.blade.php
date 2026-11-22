<div class="product-editor-row product-editor-row--spec" data-row>
    <label>Название
        <input name="specs[{{ $index }}][name]" maxlength="120" placeholder="Белок на порцию" value="{{ $spec['name'] ?? '' }}">
    </label>
    <label>Значение
        <input name="specs[{{ $index }}][value]" maxlength="5000" placeholder="25 г" value="{{ $spec['value'] ?? '' }}">
    </label>
    <button type="button" class="button-text" data-remove>Убрать</button>
</div>
