<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'price',
        'active',
    ];

    public static function toArrayIdAsKey(): array
    {
        if (! Cache::has('products')) {
            // Use keyBy for better performance than foreach loop
            $products = self::all()->keyBy('id')->all();
            Cache::put('products', $products, 1);
        }

        return Cache::get('products');
    }

    /**
     * Scope to filter only active products.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope to filter inactive products.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('active', false);
    }
}
