<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceProduct extends Model
{
    protected $table = 'invoice_products';

    public function invoice_group()
    {
        return $this->belongsTo('App\Models\InvoiceGroup');
    }

    public function productprice()
    {
        return $this->hasMany('App\Models\InvoiceProductPrice');
    }
}
