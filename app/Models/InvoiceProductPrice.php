<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceProductPrice extends Model
{
    use HasFactory;

    protected $table = 'invoice_product_prices';

    public function product()
    {
        return $this->belongsTo('App\Models\InvoiceProduct', 'invoice_product_id', 'id');
    }

    // Alias for better readability
    public function invoice_product()
    {
        return $this->product();
    }

    public function invoiceline()
    {
        return $this->hasMany('App\Models\InvoiceLine');
    }
}
