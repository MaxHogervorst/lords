<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Group;
use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class OrderService
{
    /**
     * Create an order for a member or group.
     */
    public function createOrder(
        Model $ownerable,
        Product $product,
        InvoiceGroup $invoiceGroup,
        int $amount
    ): Order {
        $order = new Order();
        $order->ownerable()->associate($ownerable);
        $order->product()->associate($product);
        $order->invoice_group()->associate($invoiceGroup);
        $order->amount = $amount;
        $order->save();

        return $order;
    }

    /**
     * Create an order for a member.
     */
    public function createMemberOrder(
        Member $member,
        Product $product,
        InvoiceGroup $invoiceGroup,
        int $amount
    ): Order {
        return $this->createOrder($member, $product, $invoiceGroup, $amount);
    }

    /**
     * Create an order for a group.
     */
    public function createGroupOrder(
        Group $group,
        Product $product,
        InvoiceGroup $invoiceGroup,
        int $amount
    ): Order {
        return $this->createOrder($group, $product, $invoiceGroup, $amount);
    }

    /**
     * Update an order.
     */
    public function updateOrder(Order $order, array $data): Order
    {
        if (isset($data['amount'])) {
            $order->amount = $data['amount'];
        }

        if (isset($data['product_id'])) {
            $order->product_id = $data['product_id'];
        }

        if (isset($data['invoice_group_id'])) {
            $order->invoice_group_id = $data['invoice_group_id'];
        }

        $order->save();

        return $order;
    }

    /**
     * Delete an order.
     */
    public function deleteOrder(Order $order): bool
    {
        return $order->delete();
    }

    /**
     * Get orders for a specific invoice group.
     */
    public function getOrdersByInvoiceGroup(InvoiceGroup $invoiceGroup): Collection
    {
        return Order::where('invoice_group_id', $invoiceGroup->id)
            ->with(['product', 'ownerable'])
            ->get();
    }

    /**
     * Get orders for a member in a specific invoice group.
     */
    public function getMemberOrdersByInvoiceGroup(Member $member, InvoiceGroup $invoiceGroup): Collection
    {
        return $member->orders()
            ->where('invoice_group_id', $invoiceGroup->id)
            ->with('product')
            ->get();
    }

    /**
     * Get orders for a group in a specific invoice group.
     */
    public function getGroupOrdersByInvoiceGroup(Group $group, InvoiceGroup $invoiceGroup): Collection
    {
        return $group->orders()
            ->where('invoice_group_id', $invoiceGroup->id)
            ->with('product')
            ->get();
    }

    /**
     * Calculate total amount for orders.
     */
    public function calculateOrdersTotal(Collection $orders): float
    {
        return $orders->sum(function (Order $order) {
            return $order->amount * $order->product->price;
        });
    }

    /**
     * Get all orders with relationships.
     */
    public function getAllWithRelations(array $relations = []): Collection
    {
        $query = Order::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Bulk create orders.
     */
    public function bulkCreateOrders(array $ordersData): Collection
    {
        $orders = collect();

        foreach ($ordersData as $orderData) {
            $order = new Order();
            $order->fill($orderData);
            $order->save();
            $orders->push($order);
        }

        return $orders;
    }
}
