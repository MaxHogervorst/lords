<?php

namespace App\Http\Controllers;

use App\Models\InvoiceGroup;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function postStore(Request $request, $type): JsonResponse
    {
        $v = Validator::make(
            $request->all(),
            [
                'memberId' => 'required|numeric',
                'product' => 'required',
                'amount' => 'required|numeric',
            ]);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        } else {
            if ($type == 'Member') {
                $type = 'App\Models\Member';
            } else {
                $type = 'App\Models\Group';
            }

            $order = new Order;
            $order->invoice_group_id = InvoiceGroup::getCurrentMonth()->id;
            $order->ownerable_id = $request->get('memberId');
            $order->ownerable_type = $type;
            $order->product_id = $request->get('product');
            $order->amount = $request->get('amount');
            $order->save();

            if ($order->exists) {
                return response()->json([
                    'success' => true,
                    'date' => date('Y-m-d G:i:s'),
                    'product' => $order->product->name,
                    'amount' => $order->amount,
                    'product_id' => $order->product->id,
                    'member_id' => $order->ownerable_id,
                    'message' => 'order successfully',
                ]);
            } else {
                return response()->json(['errors' => 'Could not be added to the database']);
            }
        }
    }
}
