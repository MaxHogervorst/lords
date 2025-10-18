<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use HasFactory;

    protected $table = 'invoice_lines';

    protected $fillable = [
        'member_id',
        'invoice_product_price_id',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function productprice(): BelongsTo
    {
        return $this->belongsTo(InvoiceProductPrice::class, 'invoice_product_price_id', 'id');
    }

    // Alias for better readability
    public function invoice_product_price(): BelongsTo
    {
        return $this->productprice();
    }
}
