<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceProductPrice extends Model {

	protected $table = 'invoice_product_prices';


    public function product()
    {
        return $this->belongsTo('App\Models\InvoiceProduct', 'invoice_product_id', 'id');
    }


    public function invoiceline()
    {
        return $this->hasMany('App\Models\InvoiceLine');
    }

}
