<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'sku', 'price', 'stock'])]
class ProductVariant extends Model
{
    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }
}
