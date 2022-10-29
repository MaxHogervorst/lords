<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\Member;
use App\Models\InvoiceGroup;
use App\Models\InvoiceProduct;
use Maatwebsite\Excel\Concerns\FromCollection;

class InvoiceExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {

        $currentmonth = InvoiceGroup::getCurrentMonth();
        $products = InvoiceProduct::where('invoice_group_id', '=', $currentmonth->id)->get();
        $header = ['Naam', 'Manor Expenses'];
        foreach($products as $product) {
            $header[] = $product->name;
        }
        $header[] = 'Totaal';
        $result = [$header];
        $total = 0;
        $member_infos = [];
        foreach (Member::with('orders.product', 'groups.orders.product', 'invoice_lines.productprice.product')->get() as $m) {
            $memberinfo = [];
            $memberinfo[] = $m->firstname . ' ' . $m->lastname;
            $manor = 0;
            $member_total = 0;

            $manor += $this->CalculateMemberOrders($m);
            $manor += $this->CalculateGroupOrders($m);

            $memberinfo[] = $manor;
            $member_total += $manor;
            $products = [];
            foreach (InvoiceProduct::where('invoice_group_id', '=', $currentmonth->id)->get() as $product) {
                $products[$product->id] = 0;
            }
            foreach ($m->invoice_lines as $il) {
                if ($il->productprice->product->invoice_group_id == $currentmonth->id) {
                    $products[$il->productprice->product->id] = $il->productprice->price;
                }
            }
            foreach ($products as $p) {
                $member_total += $p;
                $memberinfo[] = $p;
            }
            $memberinfo[] = $member_total;
            $total += $member_total;
            $member_infos[] = $memberinfo;
        }
        foreach($member_infos as $members) {
            $row = [];
            foreach($members as $m) {
                if(is_string($m)){
                  $row[] = $m;
                } else {
                    $row[] = sprintf('%.2f', $m);
                }
            }
            $result[] = $row;
        }

        return collect($result);
        
    }

    private function CalculateMemberOrders($member)
    {
        $price = 0;

        $products = Product::toArrayIdAsKey();
        foreach ($member->orders()->where('invoice_group_id', '=', InvoiceGroup::getCurrentMonth()->id)->get() as $o) {
            $price += $o->amount * $products[$o->product_id]['price'];
        }

        return $price;
    }

    private function CalculateGroupOrders($member)
    {
        $price = 0;
        $products = Product::toArrayIdAsKey();
        foreach ($member->groups()->where('invoice_group_id', '=', InvoiceGroup::getCurrentMonth()->id)->get() as $g) {
            $totalprice = 0;
            foreach ($g->orders as $o) {
                $totalprice += $o->amount * $products[$o->product_id]['price'];
            }
            $totalmebers = $g->members->count();

            $price += ($totalprice / $totalmebers);
        }

        return $price;
    }
}
