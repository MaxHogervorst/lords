<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreFiscusRequest;
use App\Http\Requests\UpdateFiscusRequest;
use App\Models\InvoiceProduct;
use App\Models\InvoiceProductPrice;
use App\Repositories\InvoiceLineRepository;
use App\Repositories\InvoiceProductRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\MemberRepository;
use App\Services\FiscusService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class FiscusController extends Controller
{
    public function __construct(
        protected FiscusService $fiscusService,
        private readonly InvoiceProductRepository $invoiceProductRepository,
        private readonly MemberRepository $memberRepository,
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceLineRepository $invoiceLineRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $currentMonth = $this->invoiceRepository->getCurrentMonth();
        $invoice_products = $this->invoiceProductRepository->getByInvoiceGroup($currentMonth);

        return view('fiscus.index')->with('invoice_products', $invoice_products);
    }

    /**
     * Display a listing of the resource.
     */
    public function create(): View
    {
        $members = $this->memberRepository->all();

        return view('fiscus.create')->with('members', $members);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFiscusRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $currentMonth = $this->invoiceRepository->getCurrentMonth();

        $result = $this->fiscusService->createInvoiceProduct($validated, $currentMonth->id);

        return response()->json([
            'success' => true,
            'message' => $result['product_name'].' Successfully added, '
                        .$result['price'].' per person.'
                        .$result['member_count'].' Total persons',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function getEdit(): View
    {
        $members = $this->memberRepository->all();
        $currentMonth = $this->invoiceRepository->getCurrentMonth();
        $invoiceproducts = $this->invoiceProductRepository->getByInvoiceGroup($currentMonth);

        return view('fiscus.edit')->with('members', $members)
            ->with('products', $invoiceproducts);
    }

    public function getInvoiceprices(InvoiceProduct $invoiceProduct): JsonResponse
    {
        return response()->json($invoiceProduct->productprice);
    }

    public function getAllinvoicelines(InvoiceProduct $invoiceProduct): JsonResponse
    {
        $lines = $this->invoiceLineRepository->getByInvoiceProduct($invoiceProduct);

        return response()->json($lines);
    }

    public function getSpecificinvoicelines(InvoiceProductPrice $invoiceProductPrice): JsonResponse
    {
        return response()->json($invoiceProductPrice->invoiceline);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFiscusRequest $request, InvoiceProduct $invoiceProduct): JsonResponse
    {
        $validated = $request->validated();
        $updatePriceId = $request->has('isupdate') ? $request->get('isupdate') : null;

        $result = $this->fiscusService->updateInvoiceProduct($invoiceProduct, $validated, $updatePriceId);

        return response()->json([
            'success' => true,
            'message' => $result['product_name'].' Successfully '.$result['update_type'].', '
                        .$result['price'].' per person.'
                        .$result['member_count'].' Total persons',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceProduct $invoiceProduct): JsonResponse
    {
        $name = $this->fiscusService->deleteInvoiceProduct($invoiceProduct);

        return response()->json(['success' => true, 'message' => $name.' Successfully deleted']);
    }
}
