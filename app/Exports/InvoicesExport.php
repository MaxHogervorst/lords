<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class InvoicesExport implements FromView, WithTitle
{
    protected $result;

    protected $products;

    protected $total;

    protected $currentmonth;

    public function __construct($result, $products, $total, $currentmonth)
    {
        $this->result = $result;
        $this->products = $products;
        $this->total = $total;
        $this->currentmonth = $currentmonth;
    }

    public function view(): View
    {
        return view('invoice.excel', [
            'result' => $this->result,
            'products' => $this->products,
            'total' => $this->total,
        ]);
    }

    public function title(): string
    {
        return 'Invoice '.$this->currentmonth->name;
    }
}
