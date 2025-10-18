<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceProduct extends Model
{
    use HasFactory;

    protected $table = 'invoice_products';

    public function invoice_group(): BelongsTo
    {
        return $this->belongsTo(InvoiceGroup::class);
    }

    public function productprice(): HasMany
    {
        return $this->hasMany(InvoiceProductPrice::class);
    }
}
