<?php

namespace App\Http\Controllers;

use App\Models\InvoiceGroup;
use App\Models\Order;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function getIndex(): View
    {
        $id = 0;
        if (InvoiceGroup::getCurrentMonth()) {
            $id = InvoiceGroup::getCurrentMonth()->id;
        }

        $orders = Order::where('invoice_group_id', '=', $id)->orderBy('id', 'DESC')->get();

        return view('home.index')->with('orders', $orders);
    }
}
