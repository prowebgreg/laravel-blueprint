<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentStatus;
use App\Traits\HasMedia;
use App\Traits\HasRelatedContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Testimonial Model
 *
 * Represents customer testimonials in the CMS (Content Resource).
 * Content Resources have NO public URL and NO SEO fields - they are
 * used to populate page sections across the site.
 *
 * Features:
 * - Polymorphic relationships to page-like models (Service, BlogPost, Page)
 * - Rating validation (1-5) enforced at database level
 * - Author metadata (name, title, location)
 * - Optional avatar image
 * - Soft delete support
 *
 * @property int $id
 * @property string $name
 * @property ContentStatus $status
 * @property string|null $author_name
 * @property string|null $author_title
 * @property string|null $location
 * @property string|null $quote
 * @property int|null $rating
 * @property string|null $avatar
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Testimonial extends Model
{
    use HasFactory;
    use HasMedia;
    use HasRelatedContent;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'testimonials';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'status',
        'author_name',
        'author_title',
        'location',
        'quote',
        'rating',
        'avatar',
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
            'rating' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
