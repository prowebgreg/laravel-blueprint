<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Setting Model
 *
 * Key-value configuration storage for application settings.
 * Uses a string 'key' as the primary key instead of auto-incrementing ID.
 *
 * Known Keys:
 * - media.fallback_image_id: UUID of the default fallback MediaAsset
 *
 * @property string $key Configuration key (dot notation)
 * @property string|null $value Configuration value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Setting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'key';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get a setting value by key.
     *
     * @param  string  $key  The setting key to retrieve
     * @param  mixed  $default  The default value to return if key doesn't exist
     * @return mixed The setting value or default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::find($key);

        if ($setting === null) {
            return $default;
        }

        return $setting->value;
    }

    /**
     * Set or update a setting value.
     *
     * @param  string  $key  The setting key to set
     * @param  mixed  $value  The value to store
     */
    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
