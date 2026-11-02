<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $all = static::allCached();
        return $all[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('system_settings_all');
    }

    public static function allCached(): array
    {
        return Cache::rememberForever('system_settings_all', function () {
            return static::pluck('value', 'key')->all();
        });
    }
}
