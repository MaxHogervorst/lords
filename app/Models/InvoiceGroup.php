<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InvoiceGroup extends Model
{
    use HasFactory;

    protected $table = 'invoice_groups';

    public static function getCurrentMonth()
    {
        if (! Cache::has('invoice_group')) {
            $invoicegroup = self::where('status', '=', true)->first();

            Cache::put('invoice_group', $invoicegroup, 1);
            if (is_null($invoicegroup)) {
                Cache::put('invoice_group', false, 1);
            }
        }

        return Cache::get('invoice_group');
    }
}
