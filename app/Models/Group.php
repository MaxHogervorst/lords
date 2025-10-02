<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    public function members()
    {
        return $this->belongsToMany('App\Models\Member')->withPivot('id', 'id');
    }

    public function orders()
    {
        return $this->morphMany('App\Models\Order', 'orderable', 'ownerable_type', 'ownerable_id');
    }

    public function invoice_group()
    {
        return $this->belongsTo('App\Models\InvoiceGroup');
    }
}
