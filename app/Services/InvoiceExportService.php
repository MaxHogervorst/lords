<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\InvoicesExport;
use App\Models\InvoiceGroup;
use App\Repositories\InvoiceProductRepository;
use App\Repositories\InvoiceRepository;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoiceExportService
{
    public function __construct(
        private readonly InvoiceCalculationService $calculationService,
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceProductRepository $invoiceProductRepository
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
}
