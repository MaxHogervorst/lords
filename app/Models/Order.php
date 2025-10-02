<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'product_id',
        'invoice_group_id',
        'ownerable_type',
        'ownerable_id',
        'amount',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ownerable(): MorphTo
    {
        return $this->morphTo();
    }

    public function invoice_group(): BelongsTo
    {
        return $this->belongsTo(InvoiceGroup::class);
    }

    /**
     * Scope to filter orders by invoice group.
     */
    public function scopeForInvoiceGroup(Builder $query, int $invoiceGroupId): Builder
    {
        return $query->where('invoice_group_id', $invoiceGroupId);
    }

    /**
     * Scope to filter orders by product.
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }
}
