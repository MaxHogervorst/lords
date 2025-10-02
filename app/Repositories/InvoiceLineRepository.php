<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\InvoiceLine;
use App\Models\InvoiceProduct;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class InvoiceLineRepository extends BaseRepository
{
    protected function makeModel(): Model
    {
        return new InvoiceLine();
    }

    /**
     * Get invoice lines by invoice product.
     */
    public function getByInvoiceProduct(InvoiceProduct $invoiceProduct, array $relations = []): Collection
    {
        $query = $this->model->whereHas('productprice', function ($q) use ($invoiceProduct) {
            $q->where('invoice_product_id', $invoiceProduct->id);
        });

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }
}
