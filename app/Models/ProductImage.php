<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['path', 'alt', 'sort_order', 'is_primary'])]
class ProductImage extends Model {}
