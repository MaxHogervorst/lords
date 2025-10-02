<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    protected $fillable = [
        'name',
        'invoice_group_id',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class)->withPivot('id', 'id');
    }

    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'orderable', 'ownerable_type', 'ownerable_id');
    }

    public function invoice_group(): BelongsTo
    {
        return $this->belongsTo(InvoiceGroup::class);
    }
}
