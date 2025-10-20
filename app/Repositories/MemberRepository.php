<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class MemberRepository extends BaseRepository
{
    protected function makeModel(): Model
    {
        return new Member();
    }

    /**
     * Find member by lastname and IBAN.
     * Performs case-insensitive search and normalizes IBAN (removes spaces).
     */
    public function findByLastnameAndIban(string $lastname, string $iban): ?Member
    {
        // Normalize inputs
        $lastname = trim($lastname);
        $iban = strtoupper(str_replace(' ', '', trim($iban)));

        return $this->model->newQuery()
            ->whereRaw('LOWER(TRIM(lastname)) = ?', [strtolower($lastname)])
            ->whereRaw('REPLACE(UPPER(iban), \' \', \'\') = ?', [$iban])
            ->first();
    }

    /**
     * Get members with RCUR status (recurring) who have activity in the current invoice group.
     */
    public function getMembersWithRcur(int $invoiceGroupId, array $relations = []): Collection
    {
        $query = $this->model->newQuery()
            ->whereNotNull('bic')
            ->whereNotNull('iban')
            ->where('had_collection', true)
            ->where(function ($query) use ($invoiceGroupId) {
                // Members with direct orders
                $query->whereHas('orders', function ($q) use ($invoiceGroupId) {
                    $q->where('invoice_group_id', $invoiceGroupId);
                })
                // OR members in groups with the invoice group
                ->orWhereHas('groups', function ($q) use ($invoiceGroupId) {
                    $q->where('invoice_group_id', $invoiceGroupId);
                })
                // OR members with invoice lines for this invoice group
                ->orWhereHas('invoice_lines.productprice.product', function ($q) use ($invoiceGroupId) {
                    $q->where('invoice_group_id', $invoiceGroupId);
                });
            });

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get members with FRST status (first time) who have activity in the current invoice group.
     */
    public function getMembersWithFrst(int $invoiceGroupId, array $relations = []): Collection
    {
        $query = $this->model->newQuery()
            ->whereNotNull('bic')
            ->whereNotNull('iban')
            ->where('had_collection', false)
            ->where(function ($query) use ($invoiceGroupId) {
                // Members with direct orders
                $query->whereHas('orders', function ($q) use ($invoiceGroupId) {
                    $q->where('invoice_group_id', $invoiceGroupId);
                })
                // OR members in groups with the invoice group
                ->orWhereHas('groups', function ($q) use ($invoiceGroupId) {
                    $q->where('invoice_group_id', $invoiceGroupId);
                })
                // OR members with invoice lines for this invoice group
                ->orWhereHas('invoice_lines.productprice.product', function ($q) use ($invoiceGroupId) {
                    $q->where('invoice_group_id', $invoiceGroupId);
                });
            });

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get members without bank information.
     */
    public function getMembersWithoutBankInfo(): Collection
    {
        return $this->model->newQuery()
            ->whereNull('bic')
            ->whereNull('iban')
            ->get();
    }

    /**
     * Get members with bank information.
     */
    public function getMembersWithBankInfo(): Collection
    {
        return $this->model->newQuery()
            ->whereNotNull('bic')
            ->whereNotNull('iban')
            ->get();
    }

    /**
     * Search members by name or email.
     */
    public function search(string $term): Collection
    {
        return $this->model->newQuery()
            ->where('firstname', 'like', "%{$term}%")
            ->orWhere('lastname', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->get();
    }

    /**
     * Find member with invoice lines by member ID.
     */
    public function findWithInvoiceLinesByMemberId(int $memberId): ?Member
    {
        return $this->model->newQuery()
            ->with('orders.product', 'groups.orders.product', 'groups.members', 'invoice_lines.productprice.product')
            ->where('id', $memberId)
            ->first();
    }

    /**
     * Get members with activity in a specific invoice group.
     * Only returns members who have orders, group memberships, or invoice lines for the given invoice group.
     * Returns query builder to allow pagination.
     */
    public function getWithActivityForInvoiceGroup(int $invoiceGroupId, array $relations = [])
    {
        $query = $this->model->newQuery()
            ->where(function ($query) use ($invoiceGroupId) {
                // Members with direct orders
                $query->whereHas('orders', function ($q) use ($invoiceGroupId) {
                    $q->where('invoice_group_id', $invoiceGroupId);
                })
                // OR members in groups with the invoice group
                ->orWhereHas('groups', function ($q) use ($invoiceGroupId) {
                    $q->where('invoice_group_id', $invoiceGroupId);
                })
                // OR members with invoice lines for this invoice group
                ->orWhereHas('invoice_lines.productprice.product', function ($q) use ($invoiceGroupId) {
                    $q->where('invoice_group_id', $invoiceGroupId);
                });
            });

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query;
    }

    /**
     * Get query builder instance (public access for complex queries).
     */
    public function query()
    {
        return $this->model->newQuery();
    }
}
