<?php

namespace App\Models;

use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSiteSetting
 */
class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory;

    public const string ANNOUNCEMENT = 'announcement';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key): ?string
    {
        return static::query()
            ->where('key', $key)
            ->value('value');
    }

    public static function setValue(string $key, ?string $value): void
    {
        if ($value === null || trim($value) === '') {
            static::query()->where('key', $key)->delete();

            return;
        }

        static::query()->updateOrCreate(
            attributes: ['key' => $key],
            values: ['value' => $value],
        );
    }
}
