<?php

declare(strict_types=1);

use App\Traits\HasContentBlocks;
use Illuminate\Database\Eloquent\Model;

/**
 * Create a test model that uses the HasContentBlocks trait
 */
class TestContentBlocksModel extends Model
{
    use HasContentBlocks;

    public $timestamps = false;

    protected $guarded = [];
}

test('getContentBlocksAttribute returns null when value is null', function () {
    $model = new TestContentBlocksModel;

    expect($model->content_blocks)->toBeNull();
});

test('getContentBlocksAttribute decodes JSON to array', function () {
    $blocks = [
        [
            'type' => 'hero',
            'data' => [
                'heading' => 'Welcome',
                'lede' => 'Test',
            ],
        ],
    ];

    $model = new TestContentBlocksModel([
        'content_blocks' => json_encode($blocks),
    ]);

    expect($model->content_blocks)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($model->content_blocks[0]['type'])->toBe('hero')
        ->and($model->content_blocks[0]['data']['heading'])->toBe('Welcome');
});

test('setContentBlocksAttribute encodes array to JSON', function () {
    $model = new TestContentBlocksModel;

    $blocks = [
        [
            'type' => 'cta',
            'data' => [
                'heading' => 'Get Started',
            ],
        ],
    ];

    $model->content_blocks = $blocks;

    // Access the raw attribute after setting
    $rawValue = $model->getAttributes()['content_blocks'] ?? null;

    expect($rawValue)
        ->toBeString()
        ->toBe(json_encode($blocks));
});

test('setContentBlocksAttribute accepts null', function () {
    $model = new TestContentBlocksModel([
        'content_blocks' => json_encode([['type' => 'hero', 'data' => []]]),
    ]);

    $model->content_blocks = null;

    $rawValue = $model->getAttributes()['content_blocks'] ?? null;

    expect($rawValue)->toBeNull();
});

test('addBlock appends block to existing blocks', function () {
    $model = new TestContentBlocksModel([
        'content_blocks' => json_encode([
            ['type' => 'hero', 'data' => ['heading' => 'Hero']],
        ]),
    ]);

    $model->addBlock([
        'type' => 'cta',
        'data' => ['heading' => 'CTA'],
    ]);

    expect($model->content_blocks)
        ->toBeArray()
        ->toHaveCount(2)
        ->and($model->content_blocks[0]['type'])->toBe('hero')
        ->and($model->content_blocks[1]['type'])->toBe('cta');
});

test('addBlock creates array when content_blocks is null', function () {
    $model = new TestContentBlocksModel;

    $model->addBlock([
        'type' => 'hero',
        'data' => ['heading' => 'First Block'],
    ]);

    expect($model->content_blocks)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($model->content_blocks[0]['type'])->toBe('hero');
});

test('removeBlock removes block at index and reindexes', function () {
    $model = new TestContentBlocksModel([
        'content_blocks' => json_encode([
            ['type' => 'hero', 'data' => []],
            ['type' => 'cta', 'data' => []],
            ['type' => 'hero', 'data' => []],
        ]),
    ]);

    $model->removeBlock(1);

    expect($model->content_blocks)
        ->toBeArray()
        ->toHaveCount(2)
        ->and($model->content_blocks[0]['type'])->toBe('hero')
        ->and($model->content_blocks[1]['type'])->toBe('hero');
});

test('removeBlock sets content_blocks to null when removing last block', function () {
    $model = new TestContentBlocksModel([
        'content_blocks' => json_encode([
            ['type' => 'hero', 'data' => []],
        ]),
    ]);

    $model->removeBlock(0);

    expect($model->content_blocks)->toBeNull();
});

test('removeBlock does nothing when index does not exist', function () {
    $model = new TestContentBlocksModel([
        'content_blocks' => json_encode([
            ['type' => 'hero', 'data' => []],
        ]),
    ]);

    $model->removeBlock(5);

    expect($model->content_blocks)
        ->toBeArray()
        ->toHaveCount(1);
});

test('reorderBlocks reorders blocks based on index mapping', function () {
    $model = new TestContentBlocksModel([
        'content_blocks' => json_encode([
            ['type' => 'hero', 'data' => ['position' => 'A']],
            ['type' => 'cta', 'data' => ['position' => 'B']],
            ['type' => 'hero', 'data' => ['position' => 'C']],
        ]),
    ]);

    // Reorder: move C to first, A to second, B to third
    $model->reorderBlocks([2, 0, 1]);

    expect($model->content_blocks)
        ->toBeArray()
        ->toHaveCount(3)
        ->and($model->content_blocks[0]['data']['position'])->toBe('C')
        ->and($model->content_blocks[1]['data']['position'])->toBe('A')
        ->and($model->content_blocks[2]['data']['position'])->toBe('B');
});

test('reorderBlocks skips invalid indices', function () {
    $model = new TestContentBlocksModel([
        'content_blocks' => json_encode([
            ['type' => 'hero', 'data' => ['position' => 'A']],
            ['type' => 'cta', 'data' => ['position' => 'B']],
        ]),
    ]);

    // Try to reorder with invalid index 5
    $model->reorderBlocks([1, 5, 0]);

    expect($model->content_blocks)
        ->toBeArray()
        ->toHaveCount(2)
        ->and($model->content_blocks[0]['data']['position'])->toBe('B')
        ->and($model->content_blocks[1]['data']['position'])->toBe('A');
});

test('reorderBlocks sets content_blocks to null when order array is empty', function () {
    $model = new TestContentBlocksModel([
        'content_blocks' => json_encode([
            ['type' => 'hero', 'data' => []],
        ]),
    ]);

    $model->reorderBlocks([]);

    expect($model->content_blocks)->toBeNull();
});
