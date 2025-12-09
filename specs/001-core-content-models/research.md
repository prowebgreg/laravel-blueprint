# Research: Core Content Models & Architecture

**Feature Branch**: `001-core-content-models`
**Date**: 2025-12-08

## Research Topics

### 1. Polymorphic Many-to-Many Relationships for Generic Relationship Engine

**Question**: How to implement a generic relationship engine where any model can relate to any other model with custom pivot data (order)?

**Decision**: Use Laravel's polymorphic many-to-many (`morphToMany` / `morphedByMany`) with a custom pivot table.

**Rationale**:
- Laravel's built-in polymorphic relationships handle the `*_type` and `*_id` columns automatically
- The `withPivot()` method allows access to additional pivot columns like `order`
- `orderByPivot()` or eager loading with `->orderBy('pivot_order')` handles sorting
- No need for custom query builders or complex manual joins

**Implementation Pattern**:
```php
// HasRelatedContent trait
public function relatedContent(string $relatedClass): MorphToMany
{
    return $this->morphToMany($relatedClass, 'source', 'content_relations', 'source_id', 'target_id')
        ->withPivot('order')
        ->orderByPivot('order');
}

public function relatedFrom(string $sourceClass): MorphToMany
{
    return $this->morphedByMany($sourceClass, 'target', 'content_relations', 'target_id', 'source_id')
        ->withPivot('order')
        ->orderByPivot('order');
}
```

**Alternatives Considered**:
- Custom pivot model with manual queries: More flexible but violates "Clarity Over Cleverness"
- Spatie laravel-model-relations package: Adds dependency, Laravel's built-in is sufficient
- Single polymorphic relation with JSON targets: Would complicate ordering and querying

---

### 2. JSONB Content Block Storage and Validation

**Question**: How to store ordered content blocks as JSONB and validate structure at write-time?

**Decision**: Use PostgreSQL JSONB column with Laravel's array cast, validate via custom validation rule.

**Rationale**:
- PostgreSQL JSONB provides efficient storage and querying of JSON data
- Laravel's `'array'` cast handles serialization/deserialization automatically
- Custom validation rule allows flexible structure validation without required fields
- Block type classes define their schema; validation checks type existence and data types

**Implementation Pattern**:
```php
// Migration
$table->jsonb('content_blocks')->nullable();

// Model cast
protected function casts(): array
{
    return [
        'content_blocks' => 'array',
    ];
}

// Custom validation rule
class ValidContentBlocks implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail('Content blocks must be an array.');
            return;
        }

        foreach ($value as $index => $block) {
            if (!isset($block['type'])) {
                $fail("Block at index {$index} must have a type.");
                continue;
            }

            $blockClass = $this->resolveBlockClass($block['type']);
            if (!$blockClass) {
                $fail("Block type '{$block['type']}' is not defined.");
                continue;
            }

            // Validate data types against block schema
            $blockClass::validateData($block['data'] ?? [], $fail, $index);
        }
    }
}
```

**Alternatives Considered**:
- Separate blocks table with polymorphic relation: Over-engineering, adds query complexity
- JSON column without validation: Risks malformed data that can't be rendered
- Required field validation: Spec explicitly states no required fields for content blocks

---

### 3. SEO Field Mirroring (OG/Twitter from Meta)

**Question**: How to implement SEO field mirroring where OG/Twitter fields default to meta fields until manually edited?

**Decision**: Use nullable database columns with accessor/mutator pattern to provide fallback values.

**Rationale**:
- Null values indicate "use default" (mirror from meta field)
- Non-null values indicate "manually set" (independent)
- Accessors provide the fallback logic transparently
- Simple to implement and understand

**Implementation Pattern**:
```php
// HasSeo trait
public function getOgTitleAttribute(?string $value): ?string
{
    return $value ?? $this->meta_title;
}

public function getOgDescriptionAttribute(?string $value): ?string
{
    return $value ?? $this->meta_description;
}

public function getTwitterTitleAttribute(?string $value): ?string
{
    return $value ?? $this->meta_title;
}

public function getTwitterDescriptionAttribute(?string $value): ?string
{
    return $value ?? $this->meta_description;
}
```

**Alternatives Considered**:
- Storing boolean flags for "is_mirrored": Adds complexity, database bloat
- Computing in controller/view: Violates "logic in models" principle
- Event-based sync: Would make fields always sync, breaking independence after manual edit

---

### 4. Slug Generation with Uniqueness and Reserved Blocking

**Question**: How to auto-generate slugs, handle duplicates with suffixes, and block reserved slugs?

**Decision**: Use Laravel's `Str::slug()` in model boot method with custom uniqueness logic.

**Rationale**:
- `Str::slug()` handles Unicode and special characters properly
- Model `creating` event ensures slug is set before save
- Uniqueness check scoped to model type (same slug allowed in different types)
- Reserved slug list in config for easy maintenance

**Implementation Pattern**:
```php
// HasSlug trait
protected static function bootHasSlug(): void
{
    static::creating(function ($model) {
        if (empty($model->slug)) {
            $model->slug = $model->generateUniqueSlug($model->name);
        }
    });
}

public function generateUniqueSlug(string $name): string
{
    $slug = Str::slug($name);

    // Check reserved slugs
    $reserved = config('content.reserved_slugs', []);
    if (in_array($slug, $reserved)) {
        throw new ValidationException::withMessages([
            'slug' => 'This slug is reserved for system use. Please choose another.',
        ]);
    }

    // Handle duplicates
    $originalSlug = $slug;
    $counter = 1;

    while (static::withTrashed()->where('slug', $slug)->exists()) {
        $counter++;
        $slug = "{$originalSlug}-{$counter}";
    }

    return $slug;
}
```

**Alternatives Considered**:
- Spatie laravel-sluggable package: Adds dependency, our needs are simpler
- Database unique constraint only: Wouldn't provide user-friendly error messages
- Global slug uniqueness: Spec explicitly allows same slug across different page types

---

### 5. Soft Delete with Configurable Recovery Period

**Question**: How to implement soft delete with configurable recovery period and relationship preservation?

**Decision**: Use Laravel's SoftDeletes trait with scheduled command for permanent purge.

**Rationale**:
- Laravel's SoftDeletes is battle-tested and well-documented
- Scheduled command allows configurable timing via environment variable
- Relationships preserved during soft-delete (no cascade) - they're hidden but restorable
- Only permanent purge (forceDelete) triggers relationship cleanup

**Implementation Pattern**:
```php
// config/content.php
'recovery_days' => env('CONTENT_RECOVERY_DAYS', 30),

// PurgeDeletedContentCommand
public function handle(): int
{
    $cutoff = now()->subDays(config('content.recovery_days'));

    $models = [Page::class, Service::class, BlogPost::class, Faq::class, Testimonial::class];

    foreach ($models as $modelClass) {
        $modelClass::onlyTrashed()
            ->where('deleted_at', '<', $cutoff)
            ->each(function ($model) {
                // Delete relationships first
                DB::table('content_relations')
                    ->where(function ($query) use ($model) {
                        $query->where('source_type', get_class($model))
                              ->where('source_id', $model->id);
                    })
                    ->orWhere(function ($query) use ($model) {
                        $query->where('target_type', get_class($model))
                              ->where('target_id', $model->id);
                    })
                    ->delete();

                // Then force delete the model
                $model->forceDelete();
            });
    }

    return Command::SUCCESS;
}
```

**Alternatives Considered**:
- Database triggers for cascade: Would bypass soft-delete period
- Model events for relationship cleanup: Would fire on soft-delete, not just purge
- Manual deletion without schedule: Inconsistent, requires admin intervention

---

### 6. Block Type Class Structure

**Question**: What structure should block type classes follow for schema definition and validation?

**Decision**: Interface-based with static schema definition and validation method.

**Rationale**:
- Interface ensures all blocks follow the same contract
- Static methods allow validation without instantiation
- Schema array defines field names and types for validation
- Keeps blocks lightweight and testable

**Implementation Pattern**:
```php
// app/Blocks/Contracts/BlockInterface.php
interface BlockInterface
{
    public static function type(): string;
    public static function schema(): array;
    public static function validateData(array $data, Closure $fail, int $index): void;
}

// app/Blocks/HeroBlock.php
class HeroBlock implements BlockInterface
{
    public static function type(): string
    {
        return 'hero';
    }

    public static function schema(): array
    {
        return [
            'heading' => 'string',
            'lede' => 'string',
            'image' => 'string',
            'cta_button_text' => 'string',
        ];
    }

    public static function validateData(array $data, Closure $fail, int $index): void
    {
        foreach ($data as $field => $value) {
            $expectedType = static::schema()[$field] ?? null;
            if ($expectedType && !self::matchesType($value, $expectedType)) {
                $fail("Block at index {$index}: field '{$field}' must be of type {$expectedType}.");
            }
        }
    }
}
```

**Alternatives Considered**:
- Abstract base class: Would complicate inheritance for simple schema definitions
- JSON schema files: Adds parsing overhead, PHP classes are more maintainable
- Database-stored schema: Violates "code-defined structure" principle

---

## Technology Decisions Summary

| Area | Decision | Package/Approach |
|------|----------|------------------|
| Relationships | Polymorphic many-to-many | Laravel built-in morphToMany |
| Content Blocks | JSONB with array cast | PostgreSQL + Laravel |
| Block Validation | Custom validation rule | Laravel Validation |
| SEO Mirroring | Nullable fields + accessors | Eloquent accessors |
| Slug Generation | Model boot method | Laravel Str::slug() |
| Soft Delete | SoftDeletes trait + command | Laravel built-in |
| Block Schema | Interface-based classes | Custom PHP classes |

## Open Questions Resolved

All technical questions have been resolved through research. No outstanding clarifications needed for Phase 1 design.
