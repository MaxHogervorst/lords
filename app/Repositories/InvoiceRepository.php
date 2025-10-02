<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\InvoiceGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
        return $this->model->where('status', true)->first();
    }

    /**
     * Get all invoice groups ordered by date.
     */
    public function getAllOrdered(string $direction = 'desc'): Collection
    {
        return $this->model->orderBy('id', $direction)->get();
    }

    /**
     * Set invoice group as active.
     */
    public function setAsActive(InvoiceGroup $invoiceGroup): InvoiceGroup
    {
        // Deactivate all other groups
        $this->model->where('status', true)->update(['status' => false]);

        // Activate the selected group
        $invoiceGroup->status = true;
        $invoiceGroup->save();

        return $invoiceGroup;
    }

    /**
     * Create new invoice group and set as active.
     */
    public function createAndSetActive(string $name): InvoiceGroup
    {
        // Deactivate all other groups
        $this->model->where('status', true)->update(['status' => false]);

        // Create new active group
        $invoiceGroup = new InvoiceGroup();
        $invoiceGroup->name = $name;
        $invoiceGroup->status = true;
        $invoiceGroup->save();

        return $invoiceGroup;
    }

    /**
     * Get invoice group by name.
     */
    public function findByName(string $name): ?InvoiceGroup
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * Check if invoice group is active.
     */
    public function isActive(InvoiceGroup $invoiceGroup): bool
    {
        return (bool) $invoiceGroup->status;
    }
}
