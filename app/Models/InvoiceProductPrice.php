<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceProductPrice extends Model
{
    use HasFactory;

    protected $table = 'invoice_product_prices';

    public function product(): BelongsTo
    {
        return $this->belongsTo(InvoiceProduct::class, 'invoice_product_id', 'id');
    }

    // Alias for better readability
    public function invoice_product(): BelongsTo
    {
        return $this->product();
    }

    public function invoiceline(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }
}
