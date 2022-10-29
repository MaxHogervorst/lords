<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    // protected $with = ['group', 'member'];

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function invoice_group()
    {
        return $this->belongsTo('App\Models\InvoiceGroup');
    }
}
