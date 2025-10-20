<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\InvoicesExport;
use App\Models\InvoiceGroup;
use App\Repositories\InvoiceProductRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\MemberRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class InvoiceExportService
{
    public function __construct(
        private readonly InvoiceCalculationService $calculationService,
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceProductRepository $invoiceProductRepository,
        private readonly MemberRepository $memberRepository
    ) {
    }

    /**
     * Generate and download Excel export for an invoice group.
     */
    public function exportToExcel(?InvoiceGroup $invoiceGroup = null): BinaryFileResponse
    {
        $invoiceGroup = $invoiceGroup ?? $this->invoiceRepository->getCurrentMonth();

        $excelData = $this->calculationService->buildExcelData($invoiceGroup);
        $products = $this->invoiceProductRepository->getByInvoiceGroup($invoiceGroup);

        return Excel::download(
            new InvoicesExport($excelData['data'], $products, $excelData['total'], $invoiceGroup),
            $invoiceGroup->name . '.xlsx'
        );
    }

    /**
     * Generate and download PDF export for an invoice group.
     * Only includes members with invoice items (orders, group orders, or invoice lines).
     */
    public function exportToPdf(?InvoiceGroup $invoiceGroup = null): Response
    {
        $invoiceGroup = $invoiceGroup ?? $this->invoiceRepository->getCurrentMonth();

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

        // Filter out members with zero total
        $membersWithActivity = $members->filter(function ($member) use ($invoiceGroup) {
            return $this->calculationService->calculateMemberTotal($member, $invoiceGroup) > 0;
        });

        $pdf = Pdf::loadView('invoice.pdf', [
            'currentmonth' => $invoiceGroup,
            'members' => $membersWithActivity
        ]);

        return $pdf->download($invoiceGroup->name . '.pdf');
    }
}
