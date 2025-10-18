<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Group;
use App\Models\InvoiceGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class GroupRepository extends BaseRepository
{
    protected function makeModel(): Model
    {
        return new Group();
    }

    /**
     * Get groups for a specific invoice group.
     */
    public function getByInvoiceGroup(InvoiceGroup $invoiceGroup, array $relations = []): Collection
    {
        $query = $this->model->newQuery()->where('invoice_group_id', $invoiceGroup->id);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get groups with their members.
     */
    public function getAllWithMembers(): Collection
    {
        return $this->model->newQuery()->with('members')->get();
    }

    /**
     * Get groups with their orders.
     */
    public function getAllWithOrders(): Collection
    {
        return $this->model->newQuery()->with('orders.product')->get();
    }

    /**
     * Search groups by name.
     */
    public function search(string $term): Collection
    {
        return $this->model->newQuery()
            ->where('name', 'like', "%{$term}%")
            ->get();
    }

    /**
     * Get groups with member count.
     */
    public function getAllWithMemberCount(): Collection
    {
        return $this->model->newQuery()->withCount('members')->get();
    }
}
