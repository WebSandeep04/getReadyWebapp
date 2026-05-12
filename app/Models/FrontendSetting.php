<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrontendSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'section',
        'label',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->where('is_active', true)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function setValue($key, $value)
    {
        $setting = self::where('key', $key)->first();
        if ($setting) {
            $setting->update(['value' => $value]);
        }
        return $setting;
    }

    /**
     * Get settings by section
     */
    public static function getBySection($section)
    {
        return self::where('section', $section)->where('is_active', true)->get();
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllAsArray()
    {
        return self::where('is_active', true)
            ->pluck('value', 'key')
            ->toArray();
    }
}
