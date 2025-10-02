<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreFiscusRequest;
use App\Http\Requests\UpdateFiscusRequest;
use App\Models\InvoiceGroup;
use App\Models\InvoiceLine;
use App\Models\InvoiceProduct;
use App\Models\InvoiceProductPrice;
use App\Models\Member;
use App\Services\FiscusService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class FiscusController extends Controller
{
    public function __construct(protected FiscusService $fiscusService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $invoice_products = InvoiceProduct::where('invoice_group_id', '=', InvoiceGroup::getCurrentMonth()->id)->get();

        return view('fiscus.index')->with('invoice_products', $invoice_products);
    }

    /**
     * Display a listing of the resource.
     */
    public function create(): View
    {
        $members = Member::all();

        return view('fiscus.create')->with('members', $members);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFiscusRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $currentmonth = InvoiceGroup::getCurrentMonth()->id;

        $result = $this->fiscusService->createInvoiceProduct($validated, $currentmonth);

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
        $members = Member::all();
        $invoiceproducts = InvoiceProduct::where('invoice_group_id', '=', InvoiceGroup::getCurrentMonth()->id)->get();

        return view('fiscus.edit')->with('members', $members)
            ->with('products', $invoiceproducts);
    }

    public function getInvoiceprices(InvoiceProduct $invoiceProduct): JsonResponse
    {
        return response()->json($invoiceProduct->productprice);
    }

    public function getAllinvoicelines(InvoiceProduct $invoiceProduct): JsonResponse
    {
        $lines = InvoiceLine::whereHas('productprice', function ($query) use ($invoiceProduct) {
            $query->where('invoice_product_id', $invoiceProduct->id);
        })->get();

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
