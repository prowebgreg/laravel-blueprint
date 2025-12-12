<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RedirectType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Redirect Model
 *
 * Stores URL redirect rules for SEO management.
 * Allows administrators to configure 301 (permanent) and 302 (temporary) redirects.
 *
 * @property int $id
 * @property string $source_path
 * @property string $target_path
 * @property RedirectType $redirect_type
 * @property bool $is_active
 * @property int $hits
 * @property \Illuminate\Support\Carbon|null $last_hit_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Redirect extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'redirects';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'source_path',
        'target_path',
        'redirect_type',
        'is_active',
        'hits',
        'last_hit_at',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'redirect_type' => RedirectType::class,
            'is_active' => 'boolean',
            'hits' => 'integer',
            'last_hit_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
