<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ProductRepository extends BaseRepository
{
    protected function makeModel(): Model
    {
        return new Product();
    }

    /**
     * Get all products as array with ID as key.
     */
    public function getAllAsArrayIdAsKey(): array
    {
        return Product::toArrayIdAsKey();
    }

    /**
     * Get active products.
     */
    public function getActive(): Collection
    {
        return $this->model->newQuery()->where('active', true)->get();
    }

    /**
     * Get products ordered by name.
     */
    public function getAllOrderedByName(string $direction = 'asc'): Collection
    {
        return $this->model->newQuery()->orderBy('name', $direction)->get();
    }

    /**
     * Get products ordered by price.
     */
    public function getAllOrderedByPrice(string $direction = 'asc'): Collection
    {
        return $this->model->newQuery()->orderBy('price', $direction)->get();
    }

    /**
     * Search products by name.
     */
    public function search(string $term): Collection
    {
        return $this->model->newQuery()
            ->where('name', 'like', "%{$term}%")
            ->get();
    }

    /**
     * Get products with their orders.
     */
    public function getAllWithOrders(): Collection
    {
        return $this->model->newQuery()->with('orders')->get();
    }

    /**
     * Get products by price range.
     */
    public function getByPriceRange(float $minPrice, float $maxPrice): Collection
    {
        return $this->model->newQuery()
            ->where('price', '>=', $minPrice)
            ->where('price', '<=', $maxPrice)
            ->get();
    }
}
