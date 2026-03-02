<?php

namespace App\Models;

use App\Models\Concerns\HasManualAutoIncrement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceGroup extends Model
{
    use HasFactory;
    use HasManualAutoIncrement;

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
