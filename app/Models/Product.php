<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function scopeWithFavoriteStatus(Builder $query, ?User $user): Builder
    {
        if ($user === null) {
            return $query;
        }

        return $query->withExists([
            'favorites as is_favorite' => fn ($favorites) => $favorites->where('user_id', $user->id),
        ]);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('id');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'product_related', 'product_id', 'related_product_id');
    }

    public function specificationRows(): array
    {
        return array_map(function (array|string $spec): array {
            if (is_array($spec)) {
                return $spec;
            }

            [$name, $value] = array_pad(explode(':', $spec, 2), 2, null);

            if ($value === null || trim($name) === '' || trim($value) === '' || mb_strlen(trim($name)) > 120) {
                return ['name' => 'Характеристика', 'value' => $spec];
            }

            return ['name' => trim($name), 'value' => trim($value)];
        }, $this->specs ?? []);
    }

    public function cover(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }
}
