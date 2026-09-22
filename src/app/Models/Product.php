<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'price',
    'description',
    'category',
    'images',
    'created_by',
    'created_by_id',
    'updated_by',
    'updated_by_id',
])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'images' => 'array',
            'created_by_id' => 'integer',
            'updated_by_id' => 'integer',
        ];
    }
}
