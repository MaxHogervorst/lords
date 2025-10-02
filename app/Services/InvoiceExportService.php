<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\InvoicesExport;
use App\Models\InvoiceGroup;
use App\Models\InvoiceProduct;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoiceExportService
{
    public function __construct(
        private readonly InvoiceCalculationService $calculationService
    ) {}

    /**
     * Generate and download Excel export for an invoice group.
     */
    public function exportToExcel(?InvoiceGroup $invoiceGroup = null): BinaryFileResponse
    {
        $invoiceGroup = $invoiceGroup ?? InvoiceGroup::getCurrentMonth();

        $excelData = $this->calculationService->buildExcelData($invoiceGroup);
        $products = InvoiceProduct::where('invoice_group_id', '=', $invoiceGroup->id)->get();

        return Excel::download(
            new InvoicesExport($excelData['data'], $products, $excelData['total'], $invoiceGroup),
            $invoiceGroup->name . '.xlsx'
        );
    }
}
