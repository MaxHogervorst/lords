<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Member extends Model
{
    use HasFactory;

    protected $table = 'members';

    protected $fillable = [
        'firstname',
        'lastname',
        'bic',
        'iban',
        'had_collection',
        'email',
    ];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'orderable', 'ownerable_type', 'ownerable_id');
    }

    public function invoice_lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function scopeFrst(Builder $query): Builder
    {
        return $query->where('had_collection', '=', false);
    }

    public function scopeRcur(Builder $query): Builder
    {
        return $query->where('had_collection', '=', true);
    }

    /**
     * Scope to filter members with bank information.
     */
    public function scopeWithBankInfo(Builder $query): Builder
    {
        return $query->whereNotNull('bic')->whereNotNull('iban');
    }

    /**
     * Scope to filter members without bank information.
     */
    public function scopeWithoutBankInfo(Builder $query): Builder
    {
        return $query->whereNull('bic')->whereNull('iban');
    }

    public function getFullName(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }
}
