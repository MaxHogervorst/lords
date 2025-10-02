<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\InvoiceProduct;
use App\Models\InvoiceGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class InvoiceProductRepository extends BaseRepository
{
    protected function makeModel(): Model
    {
        return new InvoiceProduct();
    }

    /**
     * Get invoice products by invoice group.
     */
    public function getByInvoiceGroup(InvoiceGroup $invoiceGroup, array $relations = []): Collection
    {
        $query = $this->model->where('invoice_group_id', '=', $invoiceGroup->id);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get invoice products by invoice group ID.
     */
    public function getByInvoiceGroupId(int $invoiceGroupId, array $relations = []): Collection
    {
        $query = $this->model->where('invoice_group_id', '=', $invoiceGroupId);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }
}
