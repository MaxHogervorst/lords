<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\InvoiceGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InvoiceRepository extends BaseRepository
{
    protected function makeModel(): Model
    {
        return new InvoiceGroup();
    }

    /**
     * Get current active invoice group.
     */
    public function getCurrentMonth(): ?InvoiceGroup
    {
        if (! Cache::has('invoice_group')) {
            $invoiceGroup = $this->model->newQuery()->where('status', true)->first();

            if ($invoiceGroup) {
                Cache::put('invoice_group', $invoiceGroup, 1);
            }
        }

        return Cache::get('invoice_group');
    }

    /**
     * Get all invoice groups ordered by date.
     */
    public function getAllOrdered(string $direction = 'desc'): Collection
    {
        return $this->model->newQuery()->orderBy('id', $direction)->get();
    }

    /**
     * Set invoice group as active.
     */
    public function setAsActive(InvoiceGroup $invoiceGroup): InvoiceGroup
    {
        // Deactivate all other groups
        $this->model->newQuery()->where('status', true)->update(['status' => false]);

        // Clear cache
        Cache::forget('invoice_group');

        // Activate the selected group
        $invoiceGroup->status = true;
        $invoiceGroup->save();

        // Update cache
        Cache::put('invoice_group', $invoiceGroup, 1);

        return $invoiceGroup;
    }

    /**
     * Create new invoice group and set as active.
     * Converts date input to month name format (e.g., "10-25" → "October 2025")
     */
    public function createAndSetActive(string $name): InvoiceGroup
    {
        // Convert date format to month name (e.g., "10-25" → "October 2025")
        $formattedName = $this->formatInvoiceGroupName($name);

        // Deactivate all other groups
        $this->model->newQuery()->where('status', true)->update(['status' => false]);

        // Clear cache
        Cache::forget('invoice_group');

        // Create new active group
        $invoiceGroup = new InvoiceGroup();
        $invoiceGroup->name = $formattedName;
        $invoiceGroup->status = true;
        $invoiceGroup->save();

        // Update cache
        Cache::put('invoice_group', $invoiceGroup, 1);

        return $invoiceGroup;
    }

    /**
     * Format invoice group name from date input to month name.
     * Supports formats: "10-25", "2025-10", "10/25", "October 2025", etc.
     */
    private function formatInvoiceGroupName(string $name): string
    {
        // If already in month name format, return as-is
        if (preg_match('/^[A-Za-z]+ \d{4}$/', $name)) {
            return $name;
        }

        // Try to parse various date formats
        $formats = ['m-y', 'Y-m', 'm/y', 'Y/m', 'm-Y', 'y-m'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $name);
            if ($date !== false) {
                return $date->format('F Y'); // e.g., "October 2025"
            }
        }

        // If we can't parse it, return the original name
        return $name;
    }

    /**
     * Get invoice group by name.
     */
    public function findByName(string $name): ?InvoiceGroup
    {
        return $this->model->newQuery()->where('name', $name)->first();
    }

    /**
     * Check if invoice group is active.
     */
    public function isActive(InvoiceGroup $invoiceGroup): bool
    {
        return (bool) $invoiceGroup->status;
    }
}
