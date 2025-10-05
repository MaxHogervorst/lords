<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Repositories\InvoiceProductRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\MemberRepository;
use App\Repositories\ProductRepository;

class InvoiceCalculationService
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly ProductRepository $productRepository,
        private readonly InvoiceProductRepository $invoiceProductRepository,
        private readonly MemberRepository $memberRepository
    ) {
    }
    /**
     * Calculate total amount for a member including orders, group orders, and invoice lines.
     * REQUIRES: Member must be eager loaded with 'orders', 'groups.orders', 'groups.members',
     *           and 'invoice_lines.productprice.product' relations.
     */
    public function calculateMemberTotal(Member $member, ?InvoiceGroup $invoiceGroup = null): float
    {
        $invoiceGroup = $invoiceGroup ?? $this->invoiceRepository->getCurrentMonth();

        $total = 0.0;
        $total += $this->calculateMemberOrders($member, $invoiceGroup);
        $total += $this->calculateGroupOrders($member, $invoiceGroup);
        $total += $this->calculateInvoiceLines($member, $invoiceGroup);

        return $total;
    }

    /**
     * Calculate price from member's direct orders.
     * REQUIRES: Member must be eager loaded with 'orders.product' relation.
     */
    public function calculateMemberOrders(Member $member, ?InvoiceGroup $invoiceGroup = null): float
    {
        $invoiceGroup = $invoiceGroup ?? $this->invoiceRepository->getCurrentMonth();
        $price = 0.0;

        foreach ($member->orders->where('invoice_group_id', $invoiceGroup->id) as $order) {
            if ($order->product) {
                $price += $order->amount * $order->product->price;
            }
        }

        return $price;
    }

    /**
     * Calculate price from member's group orders (split among group members).
     * REQUIRES: Member must be eager loaded with 'groups.orders.product' and 'groups.members' relations.
     */
    public function calculateGroupOrders(Member $member, ?InvoiceGroup $invoiceGroup = null): float
    {
        $invoiceGroup = $invoiceGroup ?? $this->invoiceRepository->getCurrentMonth();
        $price = 0.0;

        foreach ($member->groups->where('invoice_group_id', $invoiceGroup->id) as $group) {
            $totalPrice = 0.0;

            foreach ($group->orders as $order) {
                if ($order->product) {
                    $totalPrice += $order->amount * $order->product->price;
                }
            }

            $totalMembers = $group->members->count();

            if ($totalMembers > 0) {
                $price += ($totalPrice / $totalMembers);
            }
        }

        return $price;
    }

    /**
     * Calculate price from invoice lines.
     * REQUIRES: Member must be eager loaded with 'invoice_lines.productprice.product' relation.
     */
    public function calculateInvoiceLines(Member $member, InvoiceGroup $invoiceGroup): float
    {
        $price = 0.0;

        foreach ($member->invoice_lines as $invoiceLine) {
            if ($invoiceLine->productprice->product->invoice_group_id == $invoiceGroup->id) {
                $price += $invoiceLine->productprice->price;
            }
        }

        return $price;
    }

    /**
     * Generate member info array for export/SEPA with calculated totals.
     * REQUIRES: Member must be eager loaded with 'orders', 'groups.orders', 'groups.members',
     *           and 'invoice_lines.productprice.product' relations.
     */
    public function generateMemberInfo(Member $member, ?InvoiceGroup $invoiceGroup = null): ?array
    {
        $invoiceGroup = $invoiceGroup ?? $this->invoiceRepository->getCurrentMonth();
        $amount = $this->calculateMemberTotal($member, $invoiceGroup);

        if ($amount <= 0) {
            return null;
        }

        return [
            'name' => $member->firstname . ' ' . $member->lastname,
            'iban' => $member->iban,
            'bic' => $member->bic,
            'mandate' => $this->formatMandate($member->id),
            'amount' => $amount,
            'm' => $member,
        ];
    }

    /**
     * Format member ID as mandate with padding.
     */
    private function formatMandate(int $memberId): string
    {
        $mandatePadding = \Settings::get('mandatePadding', 8);

        return str_pad((string) $memberId, $mandatePadding, '0', STR_PAD_LEFT);
    }

    /**
     * Build Excel data for members with activity in the invoice group.
     */
    public function buildExcelData(InvoiceGroup $invoiceGroup): array
    {
        $result = [];
        $total = 0.0;

        // Only load members with activity in this invoice group
        $members = $this->memberRepository->getWithActivityForInvoiceGroup(
            $invoiceGroup->id,
            [
                'orders' => function ($query) use ($invoiceGroup) {
                    $query->where('invoice_group_id', $invoiceGroup->id);
                },
                'orders.product',
                'groups' => function ($query) use ($invoiceGroup) {
                    $query->where('invoice_group_id', $invoiceGroup->id);
                },
                'groups.orders' => function ($query) use ($invoiceGroup) {
                    $query->where('invoice_group_id', $invoiceGroup->id);
                },
                'groups.orders.product',
                'groups.members',
                'invoice_lines.productprice.product'
            ]
        )->get();

        $invoiceProducts = $this->invoiceProductRepository->getByInvoiceGroup($invoiceGroup);

        // Pre-build products array template once
        $productsTemplate = [];
        foreach ($invoiceProducts as $product) {
            $productsTemplate[$product->id] = 0;
        }

        foreach ($members as $member) {
            $memberInfo = [];
            $memberInfo[] = $member->firstname . ' ' . $member->lastname;

            // Calculate orders
            $ordersTotal = $this->calculateMemberOrders($member, $invoiceGroup)
                         + $this->calculateGroupOrders($member, $invoiceGroup);
            $memberInfo[] = $ordersTotal;

            // Build products array from template
            $products = $productsTemplate;

            foreach ($member->invoice_lines as $invoiceLine) {
                if ($invoiceLine->productprice->product->invoice_group_id == $invoiceGroup->id) {
                    $products[$invoiceLine->productprice->product->id] = $invoiceLine->productprice->price;
                }
            }

            // Add product prices to member info
            $memberTotal = $ordersTotal;
            foreach ($products as $price) {
                $memberTotal += $price;
                $memberInfo[] = $price;
            }

            $memberInfo[] = $memberTotal;
            $total += $memberTotal;
            $result[] = $memberInfo;
        }

        return [
            'data' => $result,
            'total' => $total,
        ];
    }
}
