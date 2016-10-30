<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model {

	protected $table = 'invoice_lines';

    public function member()
    {
        return $this->belongsTo('App\Models\Member');
    }
    public function productprice()
    {
        return $this->belongsTo('App\Models\InvoiceProductPrice', 'invoice_product_price_id', 'id');
    }

}
