<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\OgType;
use App\Traits\HasContentBlocks;
use App\Traits\HasRelatedContent;
use App\Traits\HasSeo;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Service Model
 *
 * Represents service offerings in the CMS (Custom Page Type)
 * that exist at /services/{slug} URLs.
 *
 * Features:
 * - SEO field mirroring for Open Graph and Twitter Cards
 * - Auto-generated unique slugs from service name
 * - JSONB content blocks for flexible page layouts
 * - Polymorphic relationships to content resources
 * - Soft delete support
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property ContentStatus $status
 * @property array|null $content_blocks
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_author
 * @property bool $meta_robots
 * @property string|null $canonical_url
 * @property string|null $og_title
 * @property string|null $og_description
 * @property OgType $og_type
 * @property string|null $og_image
 * @property string|null $twitter_title
 * @property string|null $twitter_description
 * @property string|null $twitter_image
 * @property array|null $breadcrumbs
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Service extends Model
{
    use HasContentBlocks;
    use HasFactory;
    use HasRelatedContent;
    use HasSeo;
    use HasSlug;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'services';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'status',
        'content_blocks',
        'meta_title',
        'meta_description',
        'meta_author',
        'meta_robots',
        'canonical_url',
        'og_title',
        'og_description',
        'og_type',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'breadcrumbs',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'og_type' => OgType::class,
            'meta_robots' => 'boolean',
            'breadcrumbs' => 'array',
            // content_blocks handled by HasContentBlocks trait accessor/mutator
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
