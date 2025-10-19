<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return self::where('status', '=', true)->firstOrFail();
    }
}
