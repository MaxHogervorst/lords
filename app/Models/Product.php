<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    public static function toArrayIdAsKey()
    {
        if (! Cache::has('products')) {
            $products = self::all();
            $products_new = [];
            foreach ($products as $product) {
                $products_new[$product->id] = $product;
            }
            Cache::put('products', $products_new, 1);
        }
        return Cache::get('products');
    }
}
