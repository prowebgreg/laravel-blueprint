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
 * Faq Model
 *
 * Represents frequently asked questions in the CMS (Content Resource).
 * Content Resources have NO public URL and NO SEO fields - they are
 * used to populate page sections across the site.
 *
 * Features:
 * - Polymorphic relationships to page-like models (Service, BlogPost, Page)
 * - Question and answer text fields
 * - Soft delete support
 *
 * @property int $id
 * @property string $name
 * @property ContentStatus $status
 * @property string|null $question
 * @property string|null $answer
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Faq extends Model
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
    protected $table = 'faqs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'status',
        'question',
        'answer',
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     *
     * Auto-populates the 'name' field from 'question' when creating or updating,
     * allowing the admin form to only show Question, Answer, and Status fields.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Faq $faq): void {
            if (empty($faq->name) && ! empty($faq->question)) {
                $faq->name = $faq->question;
            }
        });

        static::updating(function (Faq $faq): void {
            if ($faq->isDirty('question') && ! $faq->isDirty('name')) {
                $faq->name = $faq->question;
            }
        });
    }
}
