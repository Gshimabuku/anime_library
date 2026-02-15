<?php

namespace App\Models;

use App\Enums\SystemEnum;
use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    protected $table = 'systems';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'explanation',
        'remarks',
    ];

    public static function getString(SystemEnum $key, ?string $default = null): ?string
    {
        $value = self::query()->where('key', $key->value)->value('value');
        if (!is_string($value)) {
            return $default;
        }

        $trimmed = trim($value);
        return $trimmed === '' ? $default : $trimmed;
    }

    public static function getInt(SystemEnum $key, ?int $default = null): ?int
    {
        $value = self::query()->where('key', $key->value)->value('value');
        if ($value === null) {
            return $default;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', trim($value)) === 1) {
            return (int) trim($value);
        }

        return $default;
    }
}
