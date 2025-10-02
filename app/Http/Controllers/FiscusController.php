<?php

namespace App\Http\Controllers;

use App\Models\InvoiceGroup;
use App\Models\InvoiceLine;
use App\Models\InvoiceProduct;
use App\Models\InvoiceProductPrice;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class FiscusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $invoice_products = InvoiceProduct::where('invoice_group_id', '=', InvoiceGroup::getCurrentMonth()->id)->get();

        return view('fiscus.index')->with('invoice_products', $invoice_products);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function create()
    {
        $members = Member::all();

        return view('fiscus.create')->with('members', $members);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $v = Validator::make(Input::all(),
            [
                'finalproductname' => 'required',
                'finalproductdescription' => 'required',
                'finalpriceperperson' => 'required',
                'finalselectedmembers' => 'min:1',

            ]
        );

        if (! $v->passes()) {
            return Response::json(['errors' => $v->errors()]);
        } else {
            $currentmonth = InvoiceGroup::getCurrentMonth()->id;
            $invoiceproduct = new InvoiceProduct;
            $invoiceproduct->name = Input::get('finalproductname');
            $invoiceproduct->invoice_group_id = $currentmonth;
            $invoiceproduct->save();

            $invoiceproductprice = new InvoiceProductPrice;
            $invoiceproductprice->invoice_product_id = $invoiceproduct->id;
            $invoiceproductprice->price = Input::get('finalpriceperperson');
            $invoiceproductprice->description = Input::get('finalproductdescription');
            $invoiceproductprice->save();

            $i = 0;
            foreach (Input::get('member') as $m) {
                $invoiceline = new InvoiceLine;
                $invoiceline->invoice_product_price_id = $invoiceproductprice->id;
                $invoiceline->member_id = $m;
                $invoiceline->save();
                if ($invoiceline->exists) {
                    $i++;
                }
            }

            return Response::json(['success' => true, 'message' => $invoiceproduct->name.' Successfully added, '
                                                                        .$invoiceproductprice->price.' per person.'
                                                                        .$i.' Total persons']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getEdit()
    {
        $members = Member::all();
        $invoiceproducts = InvoiceProduct::where('invoice_group_id', '=', InvoiceGroup::getCurrentMonth()->id)->get();

        return view('fiscus.edit')->with('members', $members)
            ->with('products', $invoiceproducts);
    }

    public function getInvoiceprices($id)
    {
        return Response::json(InvoiceProductPrice::where('invoice_product_id', '=', $id)->get());
    }

    public function getAllinvoicelines($id)
    {
        $subquery = DB::table('invoice_product_prices')
            ->where('invoice_product_id', '=', $id)
            ->select('id')
            ->get();

        $query = DB::table('invoice_lines')
            ->whereIn('invoice_product_price_id', json_decode(json_encode($subquery), true))
            ->select('*')->get();

        return Response::json($query);
    }

    public function getSpecificinvoicelines($id)
    {
        return Response::json(InvoiceLine::where('invoice_product_price_id', '=', $id)->get());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $update = 'added new price';
        $v = Validator::make(Input::all(),
            [
                'finalproductdescription' => 'required',
                'finalpriceperperson' => 'required',
                'member' => 'min:1',

            ]
        );

        if (! $v->passes()) {
            return Response::json(['errors' => $v->errors()]);
        } else {
            $invoiceproduct = InvoiceProduct::find($id);

            if (Input::has('isupdate')) {
                $update = 'updated';
                $invoiceproductprice = InvoiceProductPrice::find(Input::get('isupdate'));
                if ($invoiceproductprice->exists) {
                    $invoiceproductprice->price = Input::get('finalpriceperperson');
                    $invoiceproductprice->description = Input::get('finalproductdescription');
                    $invoiceproductprice->save();

                    DB::table('invoice_lines')->where('invoice_product_price_id', '=', $invoiceproductprice->id)->delete();
                } else {
                    return Response::json(['errors' => 'Could not find Product price']);
                }
            } else {
                $invoiceproductprice = new InvoiceProductPrice;
                $invoiceproductprice->invoice_product_id = $invoiceproduct->id;
                $invoiceproductprice->price = Input::get('finalpriceperperson');
                $invoiceproductprice->description = Input::get('finalproductdescription');
                $invoiceproductprice->save();
            }

            if (Input::has('member')) {
                $i = 0;
                foreach (Input::get('member') as $m) {
                    $invoiceline = new InvoiceLine;
                    $invoiceline->invoice_product_price_id = $invoiceproductprice->id;
                    $invoiceline->member_id = $m;
                    $invoiceline->save();
                    if ($invoiceline->exists) {
                        $i++;
                    }
                }
            }

            return Response::json(['success' => true, 'message' => $invoiceproduct->name.' Successfully '.$update.', '
                                                                        .$invoiceproductprice->price.' per person.'
                                                                        .$i.' Total persons']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $product = InvoiceProduct::find(Input::get('product_id'));
        if ($product != null) {
            $name = $product->name;
            foreach ($product->productprice as $price) {
                foreach ($price->invoiceline as $line) {
                    $line->delete();
                }
                $price->delete();
            }
            $product->delete();

            return Response::json(['success' => true, 'message' => $name.' Successfully deleted']);
        } else {
            return Response::json(['success' => false, 'message' => 'Could not find product']);
        }
    }
}
