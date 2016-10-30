<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\InvoiceGroup;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use App\Models\Member;

class OrderController extends Controller {


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postStore($type)
	{
        $v = Validator::make(
            Input::all(),
            array(
                'memberId' => 'required|numeric',
                'product' => 'required',
                'amount' => 'required|numeric'
            ));

        if (!$v->passes()) {
            return Response::json(['errors' => $v->errors()]);
        } else {
            if($type == 'Member')
            {
                $type = 'App\Models\Member';
            }
            else
            {
                $type = 'App\Models\Group';
            }

            $order = new Order;
            $order->invoice_group_id = InvoiceGroup::getCurrentMonth()->id;
            $order->ownerable_id = Input::get('memberId');
            $order->ownerable_type = $type;
            $order->product_id = Input::get('product');
            $order->amount = Input::get('amount');
            $order->save();

            if ($order->exists) {
                return Response::json(array(
                            'success' => true,
                            'date' => date("Y-m-d G:i:s"),
                            'product' => $order->product->name,
                            'amount' => $order->amount,
							'product_id' => $order->product->id,
							'member_id' => $order->ownerable_id,
                            'message' => 'order successfully'
                        ));
            } else {
                return Response::json(['errors' => "Could not be added to the database"]);
            }
        }
	}

}
