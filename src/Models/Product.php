<?php

declare(strict_types=1);

namespace CleverBot\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Product model for testing database integration with tools
 *
 * @property int $id
 * @property string $name
 * @property string $category
 * @property float $price
 * @property float $discount
 */
class Product extends Model
{
    protected $fillable = [
        'name',
        'category',
        'price',
        'discount',
    ];

    protected $casts = [
        'price' => 'float',
        'discount' => 'float',
    ];
}
