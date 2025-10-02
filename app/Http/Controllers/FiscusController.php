<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFiscusRequest;
use App\Http\Requests\UpdateFiscusRequest;
use App\Models\InvoiceGroup;
use App\Models\InvoiceLine;
use App\Models\InvoiceProduct;
use App\Models\InvoiceProductPrice;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FiscusController extends Controller
{
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
        $currentmonth = InvoiceGroup::getCurrentMonth()->id;
        $invoiceproduct = new InvoiceProduct;
        $invoiceproduct->name = $request->get('finalproductname');
        $invoiceproduct->invoice_group_id = $currentmonth;
        $invoiceproduct->save();

        $invoiceproductprice = new InvoiceProductPrice;
        $invoiceproductprice->invoice_product_id = $invoiceproduct->id;
        $invoiceproductprice->price = $request->get('finalpriceperperson');
        $invoiceproductprice->description = $request->get('finalproductdescription');
        $invoiceproductprice->save();

        $i = 0;
        foreach ($request->get('member') as $m) {
            $invoiceline = new InvoiceLine;
            $invoiceline->invoice_product_price_id = $invoiceproductprice->id;
            $invoiceline->member_id = $m;
            $invoiceline->save();
            if ($invoiceline->exists) {
                $i++;
            }
        }

        return response()->json(['success' => true, 'message' => $invoiceproduct->name.' Successfully added, '
                                                                    .$invoiceproductprice->price.' per person.'
                                                                    .$i.' Total persons']);
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
    public function update(UpdateFiscusRequest $request, $id): JsonResponse
    {
        $update = 'added new price';
        $invoiceproduct = InvoiceProduct::find($id);

        if ($request->has('isupdate')) {
            $update = 'updated';
            $invoiceproductprice = InvoiceProductPrice::find($request->get('isupdate'));
            if ($invoiceproductprice->exists) {
                $invoiceproductprice->price = $request->get('finalpriceperperson');
                $invoiceproductprice->description = $request->get('finalproductdescription');
                $invoiceproductprice->save();

                InvoiceLine::where('invoice_product_price_id', '=', $invoiceproductprice->id)->delete();
            } else {
                return response()->json(['errors' => 'Could not find Product price']);
            }
        } else {
            $invoiceproductprice = new InvoiceProductPrice;
            $invoiceproductprice->invoice_product_id = $invoiceproduct->id;
            $invoiceproductprice->price = $request->get('finalpriceperperson');
            $invoiceproductprice->description = $request->get('finalproductdescription');
            $invoiceproductprice->save();
        }

        if ($request->has('member')) {
            $i = 0;
            foreach ($request->get('member') as $m) {
                $invoiceline = new InvoiceLine;
                $invoiceline->invoice_product_price_id = $invoiceproductprice->id;
                $invoiceline->member_id = $m;
                $invoiceline->save();
                if ($invoiceline->exists) {
                    $i++;
                }
            }
        }

        return response()->json(['success' => true, 'message' => $invoiceproduct->name.' Successfully '.$update.', '
                                                                    .$invoiceproductprice->price.' per person.'
                                                                    .$i.' Total persons']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $product = InvoiceProduct::find($request->get('product_id'));
        if ($product != null) {
            $name = $product->name;
            foreach ($product->productprice as $price) {
                foreach ($price->invoiceline as $line) {
                    $line->delete();
                }
                $price->delete();
            }
            $product->delete();

            return response()->json(['success' => true, 'message' => $name.' Successfully deleted']);
        } else {
            return response()->json(['success' => false, 'message' => 'Could not find product']);
        }
    }
}
