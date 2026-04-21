<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getKeyValuePairs(array $keys): array
    {
        return self::whereIn('key', $keys)
            ->get()
            ->mapWithKeys(function (self $setting) {
                return [$setting->key => $setting->decodedValue()];
            })
            ->all();
    }

    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->decodedValue();
    }

    public static function setValue(string $key, $value): self
    {
        $attributes = ['key' => $key, 'value' => static::encodedValue($key, $value)];

        return self::updateOrCreate(['key' => $key], $attributes);
    }

    protected static function encodedValue(string $key, $value): ?string
    {
        if ($key === 'mail_password' && $value !== null && $value !== '') {
            return Crypt::encryptString($value);
        }

        return $value;
    }

    protected function decodedValue()
    {
        if ($this->key === 'mail_password' && $this->value !== null && $this->value !== '') {
            try {
                return Crypt::decryptString($this->value);
            } catch (\Throwable $e) {
                return $this->value;
            }
        }

        return $this->value;
    }
}
