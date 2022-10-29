<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;

    protected $table = 'settings';

    protected $fillable = ['key', 'value'];

    public static function toMap()
    {
        $settings = [];
        foreach (Setting::all() as $setting) {
            $settings[$setting->key] = $setting->value;
        }

        return $settings;
    }
}
