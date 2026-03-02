<?php

namespace App\Models;

use App\Models\Concerns\HasManualAutoIncrement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceProduct extends Model
{
    use HasFactory;
    use HasManualAutoIncrement;

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
