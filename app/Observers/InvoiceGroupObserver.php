<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\InvoiceGroup;
use Illuminate\Support\Facades\Cache;

class InvoiceGroupObserver
{
    /**
     * Handle the InvoiceGroup "created" event.
     */
    public function created(InvoiceGroup $invoiceGroup): void
    {
        $this->clearInvoiceGroupCache();
    }

    /**
     * Handle the InvoiceGroup "updated" event.
     */
    public function updated(InvoiceGroup $invoiceGroup): void
    {
        $this->clearInvoiceGroupCache();
    }

    /**
     * Handle the InvoiceGroup "deleted" event.
     */
    public function deleted(InvoiceGroup $invoiceGroup): void
    {
        $this->clearInvoiceGroupCache();
    }

    /**
     * Handle the InvoiceGroup "restored" event.
     */
    public function restored(InvoiceGroup $invoiceGroup): void
    {
        $this->clearInvoiceGroupCache();
    }

    /**
     * Handle the InvoiceGroup "force deleted" event.
     */
    public function forceDeleted(InvoiceGroup $invoiceGroup): void
    {
        $this->clearInvoiceGroupCache();
    }

    /**
     * Clear the invoice group cache.
     */
    private function clearInvoiceGroupCache(): void
    {
        Cache::forget('invoice_group');
    }
}
