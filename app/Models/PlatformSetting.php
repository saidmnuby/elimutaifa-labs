<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $table = 'platform_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function enabled(string $key, bool $default = false): bool
    {
        $value = static::query()->whereKey($key)->value('value');

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function set(string $key, bool $enabled): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $enabled ? '1' : '0'],
        );
    }
}
