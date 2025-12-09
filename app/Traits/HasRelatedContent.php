<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Trait HasRelatedContent
 *
 * Provides polymorphic many-to-many relationships via the content_relations pivot table.
 * Enables any model to relate to or be related from other content models (Page, Service,
 * BlogPost, Faq, Testimonial) with ordered display using the pivot 'order' column.
 *
 * Relationship Types:
 * - Forward (morphToMany): This model links TO other content
 * - Inverse (morphToMany with swapped keys): Other content links TO this model
 *
 * Pivot Table: content_relations
 * - source_type/source_id: The model initiating the relationship
 * - target_type/target_id: The model being related to
 * - order: Integer for display ordering (0-indexed, default 0)
 * - created_at: Relationship creation timestamp
 *
 * Usage Examples:
 *
 * // Forward: Get FAQs related to a Service
 * $service->relatedFaqs()->get();
 *
 * // Inverse: Get Services that link to an FAQ
 * $faq->relatedFromServices()->get();
 *
 * // Attach with custom order
 * $service->attachRelated($faq, ['order' => 1]);
 *
 * // Sync relationships with pivot data
 * $service->syncRelated(Faq::class, [
 *     1 => ['order' => 0],
 *     2 => ['order' => 1],
 *     3 => ['order' => 2],
 * ]);
 *
 * Soft Delete Behavior:
 * - Relationships to soft-deleted targets excluded from normal queries
 * - Use withTrashed() on relationship to include soft-deleted targets
 * - Relationships preserved until source or target permanently purged
 */
trait HasRelatedContent
{
    /**
     * Get all FAQs related to this model.
     *
     * Forward relationship: This model links TO Faq models.
     * Results ordered by pivot 'order' column ascending.
     *
     * @return MorphToMany<\App\Models\Faq>
     */
    public function relatedFaqs(): MorphToMany
    {
        return $this->morphToMany(
            \App\Models\Faq::class,
            'source',
            'content_relations',
            'source_id',
            'target_id'
        )
            ->withPivot(['order', 'created_at', 'source_type', 'target_type'])
            ->withPivotValue('target_type', \App\Models\Faq::class)
            ->orderByPivot('order');
    }

    /**
     * Get all Testimonials related to this model.
     *
     * Forward relationship: This model links TO Testimonial models.
     * Results ordered by pivot 'order' column ascending.
     *
     * @return MorphToMany<\App\Models\Testimonial>
     */
    public function relatedTestimonials(): MorphToMany
    {
        return $this->morphToMany(
            \App\Models\Testimonial::class,
            'source',
            'content_relations',
            'source_id',
            'target_id'
        )
            ->withPivot(['order', 'created_at', 'source_type', 'target_type'])
            ->withPivotValue('target_type', \App\Models\Testimonial::class)
            ->orderByPivot('order');
    }

    /**
     * Get all Services related to this model.
     *
     * Forward relationship: This model links TO Service models.
     * Results ordered by pivot 'order' column ascending.
     *
     * @return MorphToMany<\App\Models\Service>
     */
    public function relatedServices(): MorphToMany
    {
        return $this->morphToMany(
            \App\Models\Service::class,
            'source',
            'content_relations',
            'source_id',
            'target_id'
        )
            ->withPivot(['order', 'created_at', 'source_type', 'target_type'])
            ->withPivotValue('target_type', \App\Models\Service::class)
            ->orderByPivot('order');
    }

    /**
     * Get all BlogPosts related to this model.
     *
     * Forward relationship: This model links TO BlogPost models.
     * Results ordered by pivot 'order' column ascending.
     *
     * @return MorphToMany<\App\Models\BlogPost>
     */
    public function relatedBlogPosts(): MorphToMany
    {
        return $this->morphToMany(
            \App\Models\BlogPost::class,
            'source',
            'content_relations',
            'source_id',
            'target_id'
        )
            ->withPivot(['order', 'created_at', 'source_type', 'target_type'])
            ->withPivotValue('target_type', \App\Models\BlogPost::class)
            ->orderByPivot('order');
    }

    /**
     * Get all Pages related to this model.
     *
     * Forward relationship: This model links TO Page models.
     * Results ordered by pivot 'order' column ascending.
     *
     * @return MorphToMany<\App\Models\Page>
     */
    public function relatedPages(): MorphToMany
    {
        return $this->morphToMany(
            \App\Models\Page::class,
            'source',
            'content_relations',
            'source_id',
            'target_id'
        )
            ->withPivot(['order', 'created_at', 'source_type', 'target_type'])
            ->withPivotValue('target_type', \App\Models\Page::class)
            ->orderByPivot('order');
    }

    /**
     * Get all FAQs that link TO this model.
     *
     * Inverse relationship: Faq models that have this model as their target.
     * Results ordered by pivot 'order' column ascending.
     *
     * @return MorphToMany<\App\Models\Faq>
     */
    public function relatedFromFaqs(): MorphToMany
    {
        return $this->morphToMany(
            \App\Models\Faq::class,
            'target',
            'content_relations',
            'target_id',
            'source_id'
        )
            ->withPivot(['order', 'created_at', 'source_type', 'target_type'])
            ->withPivotValue('source_type', \App\Models\Faq::class)
            ->orderByPivot('order');
    }

    /**
     * Get all Services that link TO this model.
     *
     * Inverse relationship: Service models that have this model as their target.
     * Results ordered by pivot 'order' column ascending.
     *
     * @return MorphToMany<\App\Models\Service>
     */
    public function relatedFromServices(): MorphToMany
    {
        return $this->morphToMany(
            \App\Models\Service::class,
            'target',
            'content_relations',
            'target_id',
            'source_id'
        )
            ->withPivot(['order', 'created_at', 'source_type', 'target_type'])
            ->withPivotValue('source_type', \App\Models\Service::class)
            ->orderByPivot('order');
    }

    /**
     * Get all BlogPosts that link TO this model.
     *
     * Inverse relationship: BlogPost models that have this model as their target.
     * Results ordered by pivot 'order' column ascending.
     *
     * @return MorphToMany<\App\Models\BlogPost>
     */
    public function relatedFromBlogPosts(): MorphToMany
    {
        return $this->morphToMany(
            \App\Models\BlogPost::class,
            'target',
            'content_relations',
            'target_id',
            'source_id'
        )
            ->withPivot(['order', 'created_at', 'source_type', 'target_type'])
            ->withPivotValue('source_type', \App\Models\BlogPost::class)
            ->orderByPivot('order');
    }

    /**
     * Get all Pages that link TO this model.
     *
     * Inverse relationship: Page models that have this model as their target.
     * Results ordered by pivot 'order' column ascending.
     *
     * @return MorphToMany<\App\Models\Page>
     */
    public function relatedFromPages(): MorphToMany
    {
        return $this->morphToMany(
            \App\Models\Page::class,
            'target',
            'content_relations',
            'target_id',
            'source_id'
        )
            ->withPivot(['order', 'created_at', 'source_type', 'target_type'])
            ->withPivotValue('source_type', \App\Models\Page::class)
            ->orderByPivot('order');
    }

    /**
     * Get all Testimonials that link TO this model.
     *
     * Inverse relationship: Testimonial models that have this model as their target.
     * Results ordered by pivot 'order' column ascending.
     *
     * @return MorphToMany<\App\Models\Testimonial>
     */
    public function relatedFromTestimonials(): MorphToMany
    {
        return $this->morphToMany(
            \App\Models\Testimonial::class,
            'target',
            'content_relations',
            'target_id',
            'source_id'
        )
            ->withPivot(['order', 'created_at', 'source_type', 'target_type'])
            ->withPivotValue('source_type', \App\Models\Testimonial::class)
            ->orderByPivot('order');
    }

    /**
     * Attach one or more related models with optional pivot data.
     *
     * Accepts a single Model or Collection of Models to attach as targets.
     * Duplicate relationships are silently ignored (unique constraint on pivot).
     *
     * Example:
     *     $service->attachRelated($faq, ['order' => 0]);
     *     $service->attachRelated($faqCollection, ['order' => 1]);
     *
     * @param  Collection<int, Model>|Model  $targets  Model(s) to attach
     * @param  array<string, mixed>  $pivotData  Optional pivot data (e.g., ['order' => 1])
     */
    public function attachRelated(Collection|Model $targets, array $pivotData = []): void
    {
        if ($targets instanceof Model) {
            $targets = collect([$targets]);
        }

        foreach ($targets as $target) {
            $relationshipMethod = $this->getRelationshipMethodForModel($target);

            if ($relationshipMethod !== null) {
                $this->$relationshipMethod()->attach($target->getKey(), $pivotData);
            }
        }
    }

    /**
     * Detach one or more related models.
     *
     * Accepts a single Model or Collection of Models to detach.
     * Non-existent relationships are silently ignored.
     *
     * Example:
     *     $service->detachRelated($faq);
     *     $service->detachRelated($faqCollection);
     *
     * @param  Collection<int, Model>|Model  $targets  Model(s) to detach
     */
    public function detachRelated(Collection|Model $targets): void
    {
        if ($targets instanceof Model) {
            $targets = collect([$targets]);
        }

        foreach ($targets as $target) {
            $relationshipMethod = $this->getRelationshipMethodForModel($target);

            if ($relationshipMethod !== null) {
                $this->$relationshipMethod()->detach($target->getKey());
            }
        }
    }

    /**
     * Sync relationships for a specific model class with pivot data.
     *
     * Replaces all existing relationships to the specified model type with the
     * provided IDs and pivot data. Existing relationships to other model types
     * remain unchanged.
     *
     * Example:
     *     $service->syncRelated(Faq::class, [
     *         1 => ['order' => 0],
     *         2 => ['order' => 1],
     *         3 => ['order' => 2],
     *     ]);
     *
     * @param  class-string<Model>  $relatedClass  Fully qualified class name (e.g., \App\Models\Faq::class)
     * @param  array<int, array<string, mixed>>  $idsWithPivot  Array of IDs with pivot data
     */
    public function syncRelated(string $relatedClass, array $idsWithPivot): void
    {
        $relationshipMethod = $this->getRelationshipMethodForClass($relatedClass);

        if ($relationshipMethod !== null) {
            $this->$relationshipMethod()->sync($idsWithPivot);
        }
    }

    /**
     * Get the relationship method name for a given model instance.
     *
     * Maps model instances to their corresponding forward relationship method.
     *
     * @param  Model  $model  The model instance
     * @return string|null The relationship method name or null if not supported
     */
    protected function getRelationshipMethodForModel(Model $model): ?string
    {
        return match ($model::class) {
            \App\Models\Faq::class => 'relatedFaqs',
            \App\Models\Testimonial::class => 'relatedTestimonials',
            \App\Models\Service::class => 'relatedServices',
            \App\Models\BlogPost::class => 'relatedBlogPosts',
            \App\Models\Page::class => 'relatedPages',
            default => null,
        };
    }

    /**
     * Get the relationship method name for a given model class.
     *
     * Maps fully qualified class names to their corresponding forward relationship method.
     *
     * @param  class-string<Model>  $class  The fully qualified class name
     * @return string|null The relationship method name or null if not supported
     */
    protected function getRelationshipMethodForClass(string $class): ?string
    {
        return match ($class) {
            \App\Models\Faq::class => 'relatedFaqs',
            \App\Models\Testimonial::class => 'relatedTestimonials',
            \App\Models\Service::class => 'relatedServices',
            \App\Models\BlogPost::class => 'relatedBlogPosts',
            \App\Models\Page::class => 'relatedPages',
            default => null,
        };
    }
}
