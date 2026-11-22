<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['category_id', 'brand_id', 'name', 'slug', 'description', 'composition', 'specs', 'price', 'stock', 'is_active'])]
class Product extends Model
{
    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'specs' => 'array', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function cover(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }
}
