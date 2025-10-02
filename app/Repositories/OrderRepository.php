<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\InvoiceGroup;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class OrderRepository extends BaseRepository
{
    protected function makeModel(): Model
    {
        return new Order();
    }

    /**
     * Get orders by invoice group.
     */
    public function getByInvoiceGroup(InvoiceGroup $invoiceGroup, array $relations = []): Collection
    {
        $query = $this->model->where('invoice_group_id', $invoiceGroup->id);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get orders by product.
     */
    public function getByProduct(Product $product, array $relations = []): Collection
    {
        $query = $this->model->where('product_id', $product->id);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get orders for a specific ownerable (Member or Group).
     */
    public function getByOwnerable(Model $ownerable, array $relations = []): Collection
    {
        $query = $this->model
            ->where('ownerable_type', get_class($ownerable))
            ->where('ownerable_id', $ownerable->id);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get orders by ownerable and invoice group.
     */
    public function getByOwnerableAndInvoiceGroup(
        Model $ownerable,
        InvoiceGroup $invoiceGroup,
        array $relations = []
    ): Collection {
        $query = $this->model
            ->where('ownerable_type', get_class($ownerable))
            ->where('ownerable_id', $ownerable->id)
            ->where('invoice_group_id', $invoiceGroup->id);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Calculate total for orders.
     */
    public function calculateTotal(Collection $orders): float
    {
        return $orders->sum(function (Order $order) {
            return $order->amount * $order->product->price;
        });
    }

    /**
     * Get all orders with relationships.
     */
    public function getAllWithRelations(): Collection
    {
        return $this->model
            ->with(['product', 'ownerable', 'invoice_group'])
            ->get();
    }
}
