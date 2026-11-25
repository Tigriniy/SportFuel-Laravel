<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['status', 'delivery_method', 'payment_method', 'delivery_address', 'contact_phone', 'comment', 'delivery_fee', 'total_amount'])]
class Order extends Model
{
    public const PENDING = 'pending';

    public const FREE_DELIVERY_AT = 300000;

    public const DELIVERY = [
        'courier' => ['label' => 'Курьерская доставка', 'fee' => 30000],
        'pickup' => ['label' => 'Пункт выдачи', 'fee' => 30000],
        'post' => ['label' => 'Почта России', 'fee' => 25000],
        'express' => ['label' => 'Экспресс-доставка по Томску и области', 'fee' => 50000],
    ];

    public const PAYMENT = [
        'cash' => 'Наличными при получении',
        'card' => 'Картой при получении',
    ];

    public static function deliveryFee(string $method, int $subtotal): int
    {
        if ($method !== 'express' && $subtotal >= self::FREE_DELIVERY_AT) {
            return 0;
        }

        return self::DELIVERY[$method]['fee'];
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::PENDING => 'Ожидает обработки',
            default => 'Статус уточняется',
        };
    }

    protected function casts(): array
    {
        return ['total_amount' => 'decimal:2', 'delivery_fee' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
