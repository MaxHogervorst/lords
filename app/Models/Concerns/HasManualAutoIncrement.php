<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;

trait HasManualAutoIncrement
{
    protected static function bootHasManualAutoIncrement(): void
    {
        static::creating(function ($model) {
            $key = $model->getKeyName();
            if (empty($model->{$key})) {
                $model->{$key} = (int) DB::table($model->getTable())->max($key) + 1;
            }
        });
    }
}
