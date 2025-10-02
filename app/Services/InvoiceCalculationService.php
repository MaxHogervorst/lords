<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InvoiceGroup;
use App\Models\InvoiceProduct;
use App\Models\Member;
use App\Models\Product;

class InvoiceCalculationService
{
    /**
     * Calculate total amount for a member including orders, group orders, and invoice lines.
     */
    public function calculateMemberTotal(Member $member, ?InvoiceGroup $invoiceGroup = null): float
    {
        $invoiceGroup = $invoiceGroup ?? InvoiceGroup::getCurrentMonth();

        $total = 0.0;
        $total += $this->calculateMemberOrders($member, $invoiceGroup);
        $total += $this->calculateGroupOrders($member, $invoiceGroup);
        $total += $this->calculateInvoiceLines($member, $invoiceGroup);

        return $total;
    }

    /**
     * Calculate price from member's direct orders.
     */
    public function calculateMemberOrders(Member $member, ?InvoiceGroup $invoiceGroup = null): float
    {
        $invoiceGroup = $invoiceGroup ?? InvoiceGroup::getCurrentMonth();
        $price = 0.0;
        $products = Product::toArrayIdAsKey();

        foreach ($member->orders()->where('invoice_group_id', '=', $invoiceGroup->id)->get() as $order) {
            $price += $order->amount * $products[$order->product_id]['price'];
        }

        return $price;
    }

    /**
     * Calculate price from member's group orders (split among group members).
     */
    public function calculateGroupOrders(Member $member, ?InvoiceGroup $invoiceGroup = null): float
    {
        $invoiceGroup = $invoiceGroup ?? InvoiceGroup::getCurrentMonth();
        $price = 0.0;
        $products = Product::toArrayIdAsKey();

        foreach ($member->groups()->where('invoice_group_id', '=', $invoiceGroup->id)->get() as $group) {
            $totalPrice = 0.0;
            foreach ($group->orders as $order) {
                $totalPrice += $order->amount * $products[$order->product_id]['price'];
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
     */
    public function generateMemberInfo(Member $member, ?InvoiceGroup $invoiceGroup = null): ?array
    {
        $invoiceGroup = $invoiceGroup ?? InvoiceGroup::getCurrentMonth();
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
     * Build Excel data for all members with invoice products breakdown.
     */
    public function buildExcelData(InvoiceGroup $invoiceGroup): array
    {
        $result = [];
        $total = 0.0;

        $members = Member::with('orders.product', 'groups.orders.product', 'invoice_lines.productprice.product')->get();
        $invoiceProducts = InvoiceProduct::where('invoice_group_id', '=', $invoiceGroup->id)->get();

        foreach ($members as $member) {
            $memberInfo = [];
            $memberInfo[] = $member->firstname . ' ' . $member->lastname;

            // Calculate orders
            $ordersTotal = $this->calculateMemberOrders($member, $invoiceGroup)
                         + $this->calculateGroupOrders($member, $invoiceGroup);
            $memberInfo[] = $ordersTotal;

            // Build products array
            $products = [];
            foreach ($invoiceProducts as $product) {
                $products[$product->id] = 0;
            }

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
