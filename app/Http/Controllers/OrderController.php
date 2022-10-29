<?php

namespace App\Http\Controllers;

use App\Models\InvoiceGroup;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postStore(Request $request, $type)
    {
        $v = Validator::make(
            $request->all(),
            [
                'memberId' => 'required|numeric',
                'product' => 'required',
                'amount' => 'required|numeric',
            ]);

        if (! $v->passes()) {
            return Response::json(['errors' => $v->errors()]);
        } else {
            if ($type == 'Member') {
                $type = 'App\Models\Member';
            } else {
                $type = 'App\Models\Group';
            }

            $order = new Order;
            $order->invoice_group_id = InvoiceGroup::getCurrentMonth()->id;
            $order->ownerable_id = $request->input('memberId');
            $order->ownerable_type = $type;
            $order->product_id = $request->input('product');
            $order->amount = $request->input('amount');
            $order->save();

            if ($order->exists) {
                return Response::json([
                    'success' => true,
                    'date' => date('Y-m-d G:i:s'),
                    'product' => $order->product->name,
                    'amount' => $order->amount,
                    'product_id' => $order->product->id,
                    'member_id' => $order->ownerable_id,
                    'message' => 'order successfully',
                ]);
            } else {
                return Response::json(['errors' => 'Could not be added to the database']);
            }
        }
    }
}
