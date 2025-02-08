<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // Assuming the table name is 'settings'
    protected $table = 'settings';

    // Specify fillable fields if you want to use mass assignment
    protected $fillable = ['name', 'value'];

    // Method to get the value of a setting by its name
    public static function get($name, $default = null)
    {
        $setting = self::where('name', $name)->first();
        return $setting ? $setting->value : $default;
    }

    // Method to set or update the value of a setting by its name
    public static function set($name, $value)
    {
        return self::updateOrCreate(['name' => $name], ['value' => $value]);
    }
}

