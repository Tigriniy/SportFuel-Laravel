<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'variant_id', 'item_key', 'quantity'])]
class CartItem extends Model
{
    public const MAX_QUANTITY = 9999;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function availableQuantity(): int
    {
        if (! $this->product->is_active || ($this->variant_id === null && $this->product->variants_exists)) {
            return 0;
        }

        return min((int) ($this->variant?->stock ?? $this->product->stock), self::MAX_QUANTITY);
    }

    public function unitPrice(): string
    {
        return $this->variant?->price ?? $this->product->price;
    }

    public function subtotalInKopecks(): int
    {
        [$rubles, $kopecks] = explode('.', $this->unitPrice());

        return ((int) $rubles * 100 + (int) $kopecks) * $this->quantity;
    }
}
