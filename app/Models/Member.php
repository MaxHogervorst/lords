<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = 'members';

    public function groups()
    {
        return $this->belongsToMany('App\Models\Group');
    }

    public function orders()
    {
        return $this->morphMany('App\Models\Order', 'orderable', 'ownerable_type', 'ownerable_id');
    }

    public function invoice_lines()
    {
        return $this->hasMany('App\Models\InvoiceLine');
    }

    public function scopeFrst($query)
    {
        return $query->where('had_collection', '=', false);
    }

    public function scopeRcur($query)
    {
        return $query->where('had_collection', '=', true);
    }

    public function getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
