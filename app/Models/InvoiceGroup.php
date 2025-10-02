<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InvoiceGroup extends Model
{
    use HasFactory;

    protected $table = 'invoice_groups';

    protected $fillable = [
        'name',
        'status',
    ];

    public static function getCurrentMonth(): self
    {
        if (! Cache::has('invoice_group')) {
            $invoicegroup = self::where('status', '=', true)->firstOrFail();

            Cache::put('invoice_group', $invoicegroup, 1);
        }

        return Cache::get('invoice_group');
    }
}
