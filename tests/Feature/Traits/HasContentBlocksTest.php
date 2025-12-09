<?php

declare(strict_types=1);

use App\Traits\HasContentBlocks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/**
 * Test model that uses the HasContentBlocks trait.
 */
class TestBlocksModel extends Model
{
    use HasContentBlocks;

    protected $table = 'test_blocks_models';

    protected $guarded = [];
}

beforeEach(function () {
    Schema::create('test_blocks_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->jsonb('content_blocks')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_blocks_models');
});

describe('getContentBlocksAttribute', function () {
    test('returns null when content_blocks is null', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => null,
        ]);

        expect($model->content_blocks)->toBeNull();
    });

    test('returns array for valid JSON string', function () {
        $blocks = [
            ['type' => 'hero', 'data' => ['heading' => 'Welcome']],
        ];

        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $blocks,
        ]);

        $fresh = $model->fresh();
        // Use toEqual for value comparison (PostgreSQL JSONB may reorder keys)
        expect($fresh->content_blocks)->toEqual($blocks);
    });

    test('returns array when value is already an array', function () {
        $model = new TestBlocksModel(['name' => 'Test']);
        $blocks = [['type' => 'hero', 'data' => []]];
        $model->content_blocks = $blocks;

        expect($model->content_blocks)->toBe($blocks);
    });

    test('returns null for invalid JSON', function () {
        $model = TestBlocksModel::create(['name' => 'Test']);

        // Use reflection to set invalid JSON in attributes
        $reflection = new ReflectionClass($model);
        $property = $reflection->getProperty('attributes');
        $property->setAccessible(true);
        $attrs = $property->getValue($model);
        $attrs['content_blocks'] = 'invalid json{';
        $property->setValue($model, $attrs);

        expect($model->content_blocks)->toBeNull();
    });

    test('returns empty array for empty JSON array', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [],
        ]);

        $fresh = $model->fresh();
        expect($fresh->content_blocks)->toBe([]);
    });

    test('handles complex nested data structures', function () {
        $blocks = [
            [
                'type' => 'hero',
                'data' => [
                    'heading' => 'Welcome',
                    'nested' => [
                        'level1' => [
                            'level2' => ['value' => 'deep'],
                        ],
                    ],
                ],
            ],
        ];

        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $blocks,
        ]);

        $fresh = $model->fresh();
        // Use toEqual for value comparison (PostgreSQL JSONB may reorder keys)
        expect($fresh->content_blocks)->toEqual($blocks);
    });
});

describe('setContentBlocksAttribute', function () {
    test('sets to null when given null', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [['type' => 'hero', 'data' => []]],
        ]);

        $model->content_blocks = null;
        $model->save();

        expect($model->fresh()->content_blocks)->toBeNull();
    });

    test('stores JSON string directly', function () {
        $model = new TestBlocksModel(['name' => 'Test']);
        $json = '[{"type":"hero","data":{}}]';
        $model->content_blocks = $json;

        // Use reflection to check internal attributes
        $reflection = new ReflectionClass($model);
        $property = $reflection->getProperty('attributes');
        $property->setAccessible(true);
        $attrs = $property->getValue($model);

        expect($attrs['content_blocks'])->toBe($json);
    });

    test('encodes array to JSON', function () {
        $blocks = [['type' => 'hero', 'data' => ['heading' => 'Test']]];
        $model = new TestBlocksModel(['name' => 'Test']);
        $model->content_blocks = $blocks;

        // Use reflection to check internal attributes
        $reflection = new ReflectionClass($model);
        $property = $reflection->getProperty('attributes');
        $property->setAccessible(true);
        $attrs = $property->getValue($model);

        expect($attrs['content_blocks'])->toBe(json_encode($blocks));
    });

    test('stores empty array correctly', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [],
        ]);

        expect($model->fresh()->content_blocks)->toBe([]);
    });

    test('handles various data types in blocks', function () {
        $blocks = [
            [
                'type' => 'mixed',
                'data' => [
                    'string' => 'text',
                    'integer' => 42,
                    'float' => 3.14,
                    'boolean' => true,
                    'null' => null,
                    'array' => [1, 2, 3],
                ],
            ],
        ];

        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $blocks,
        ]);

        $fresh = $model->fresh();
        // Use toEqual for value comparison (PostgreSQL JSONB may reorder keys)
        expect($fresh->content_blocks)->toEqual($blocks);
    });
});

describe('addBlock', function () {
    test('adds block to empty content_blocks', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => null,
        ]);

        $model->addBlock(['type' => 'hero', 'data' => ['heading' => 'New']]);

        expect($model->content_blocks)->toBe([
            ['type' => 'hero', 'data' => ['heading' => 'New']],
        ]);
    });

    test('appends to existing array', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [
                ['type' => 'hero', 'data' => ['heading' => 'First']],
            ],
        ]);

        $model->addBlock(['type' => 'cta', 'data' => ['heading' => 'Second']]);

        expect($model->content_blocks)->toHaveCount(2);
        expect($model->content_blocks[1]['type'])->toBe('cta');
    });

    test('adds multiple blocks sequentially', function () {
        $model = TestBlocksModel::create(['name' => 'Test', 'content_blocks' => null]);

        $model->addBlock(['type' => 'hero', 'data' => []]);
        $model->addBlock(['type' => 'cta', 'data' => []]);
        $model->addBlock(['type' => 'hero', 'data' => []]);

        expect($model->content_blocks)->toHaveCount(3);
    });

    test('preserves existing blocks', function () {
        $existing = [
            ['type' => 'hero', 'data' => ['heading' => 'Keep Me']],
        ];
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $existing,
        ]);

        $model->addBlock(['type' => 'cta', 'data' => []]);

        expect($model->content_blocks[0])->toBe($existing[0]);
    });

    test('handles missing data field', function () {
        $model = TestBlocksModel::create(['name' => 'Test', 'content_blocks' => null]);

        $model->addBlock(['type' => 'hero']);

        expect($model->content_blocks)->toHaveCount(1);
        expect($model->content_blocks[0]['type'])->toBe('hero');
    });

    test('handles empty data field', function () {
        $model = TestBlocksModel::create(['name' => 'Test', 'content_blocks' => null]);

        $model->addBlock(['type' => 'hero', 'data' => []]);

        expect($model->content_blocks[0]['data'])->toBe([]);
    });

    test('persists after save', function () {
        $model = TestBlocksModel::create(['name' => 'Test', 'content_blocks' => null]);

        $model->addBlock(['type' => 'hero', 'data' => ['heading' => 'Test']]);
        $model->save();

        $fresh = $model->fresh();
        expect($fresh->content_blocks)->toHaveCount(1);
        expect($fresh->content_blocks[0]['type'])->toBe('hero');
    });
});

describe('removeBlock', function () {
    test('removes block at specified index', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [
                ['type' => 'hero', 'data' => []],
                ['type' => 'cta', 'data' => []],
                ['type' => 'hero', 'data' => []],
            ],
        ]);

        $model->removeBlock(1);

        expect($model->content_blocks)->toHaveCount(2);
        expect($model->content_blocks[0]['type'])->toBe('hero');
        expect($model->content_blocks[1]['type'])->toBe('hero');
    });

    test('reindexes array correctly', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [
                ['type' => 'a', 'data' => []],
                ['type' => 'b', 'data' => []],
                ['type' => 'c', 'data' => []],
            ],
        ]);

        $model->removeBlock(0);

        expect(array_keys($model->content_blocks))->toBe([0, 1]);
        expect($model->content_blocks[0]['type'])->toBe('b');
        expect($model->content_blocks[1]['type'])->toBe('c');
    });

    test('sets to null when removing last block', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [['type' => 'hero', 'data' => []]],
        ]);

        $model->removeBlock(0);

        expect($model->content_blocks)->toBeNull();
    });

    test('handles invalid index gracefully', function () {
        $blocks = [['type' => 'hero', 'data' => []]];
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $blocks,
        ]);

        $model->removeBlock(99);

        expect($model->content_blocks)->toBe($blocks);
    });

    test('handles negative index gracefully', function () {
        $blocks = [['type' => 'hero', 'data' => []]];
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $blocks,
        ]);

        $model->removeBlock(-1);

        expect($model->content_blocks)->toBe($blocks);
    });

    test('handles null content_blocks', function () {
        $model = TestBlocksModel::create(['name' => 'Test', 'content_blocks' => null]);

        $model->removeBlock(0);

        expect($model->content_blocks)->toBeNull();
    });

    test('removes first block correctly', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [
                ['type' => 'first', 'data' => []],
                ['type' => 'second', 'data' => []],
            ],
        ]);

        $model->removeBlock(0);

        expect($model->content_blocks)->toHaveCount(1);
        expect($model->content_blocks[0]['type'])->toBe('second');
    });

    test('removes last block correctly', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [
                ['type' => 'first', 'data' => []],
                ['type' => 'second', 'data' => []],
            ],
        ]);

        $model->removeBlock(1);

        expect($model->content_blocks)->toHaveCount(1);
        expect($model->content_blocks[0]['type'])->toBe('first');
    });
});

describe('reorderBlocks', function () {
    test('reorders based on index mapping', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [
                ['type' => 'a', 'data' => []],
                ['type' => 'b', 'data' => []],
                ['type' => 'c', 'data' => []],
            ],
        ]);

        $model->reorderBlocks([2, 0, 1]);

        expect($model->content_blocks[0]['type'])->toBe('c');
        expect($model->content_blocks[1]['type'])->toBe('a');
        expect($model->content_blocks[2]['type'])->toBe('b');
    });

    test('handles reverse order', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [
                ['type' => 'a', 'data' => []],
                ['type' => 'b', 'data' => []],
                ['type' => 'c', 'data' => []],
            ],
        ]);

        $model->reorderBlocks([2, 1, 0]);

        expect($model->content_blocks[0]['type'])->toBe('c');
        expect($model->content_blocks[1]['type'])->toBe('b');
        expect($model->content_blocks[2]['type'])->toBe('a');
    });

    test('skips invalid indices', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [
                ['type' => 'a', 'data' => []],
                ['type' => 'b', 'data' => []],
            ],
        ]);

        $model->reorderBlocks([0, 99, 1]);

        expect($model->content_blocks)->toHaveCount(2);
        expect($model->content_blocks[0]['type'])->toBe('a');
        expect($model->content_blocks[1]['type'])->toBe('b');
    });

    test('sets to null for empty order array', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [['type' => 'hero', 'data' => []]],
        ]);

        $model->reorderBlocks([]);

        expect($model->content_blocks)->toBeNull();
    });

    test('handles single block reorder', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [['type' => 'hero', 'data' => []]],
        ]);

        $model->reorderBlocks([0]);

        expect($model->content_blocks)->toHaveCount(1);
        expect($model->content_blocks[0]['type'])->toBe('hero');
    });

    test('handles partial reordering', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [
                ['type' => 'a', 'data' => []],
                ['type' => 'b', 'data' => []],
                ['type' => 'c', 'data' => []],
            ],
        ]);

        // Only include indices 2 and 0, skipping 1
        $model->reorderBlocks([2, 0]);

        expect($model->content_blocks)->toHaveCount(2);
        expect($model->content_blocks[0]['type'])->toBe('c');
        expect($model->content_blocks[1]['type'])->toBe('a');
    });

    test('handles null content_blocks', function () {
        $model = TestBlocksModel::create(['name' => 'Test', 'content_blocks' => null]);

        $model->reorderBlocks([0, 1, 2]);

        expect($model->content_blocks)->toBeNull();
    });

    test('maintains correct order with complex rearrangement', function () {
        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => [
                ['type' => '0', 'data' => []],
                ['type' => '1', 'data' => []],
                ['type' => '2', 'data' => []],
                ['type' => '3', 'data' => []],
                ['type' => '4', 'data' => []],
            ],
        ]);

        $model->reorderBlocks([4, 2, 0, 3, 1]);

        expect($model->content_blocks[0]['type'])->toBe('4');
        expect($model->content_blocks[1]['type'])->toBe('2');
        expect($model->content_blocks[2]['type'])->toBe('0');
        expect($model->content_blocks[3]['type'])->toBe('3');
        expect($model->content_blocks[4]['type'])->toBe('1');
    });
});

describe('stress tests and edge cases', function () {
    test('handles large number of blocks', function () {
        $blocks = [];
        for ($i = 0; $i < 100; $i++) {
            $blocks[] = ['type' => 'block', 'data' => ['index' => $i]];
        }

        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $blocks,
        ]);

        $fresh = $model->fresh();
        expect($fresh->content_blocks)->toHaveCount(100);
        expect($fresh->content_blocks[50]['data']['index'])->toBe(50);
    });

    test('handles deeply nested data structures', function () {
        $blocks = [
            [
                'type' => 'complex',
                'data' => [
                    'l1' => [
                        'l2' => [
                            'l3' => [
                                'l4' => [
                                    'l5' => 'deep value',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $blocks,
        ]);

        $fresh = $model->fresh();
        expect($fresh->content_blocks[0]['data']['l1']['l2']['l3']['l4']['l5'])->toBe('deep value');
    });

    test('handles unicode content', function () {
        $blocks = [
            [
                'type' => 'unicode',
                'data' => [
                    'japanese' => 'こんにちは',
                    'chinese' => '你好',
                    'emoji' => '😀🎉🚀',
                    'arabic' => 'مرحبا',
                ],
            ],
        ];

        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $blocks,
        ]);

        $fresh = $model->fresh();
        expect($fresh->content_blocks[0]['data']['japanese'])->toBe('こんにちは');
        expect($fresh->content_blocks[0]['data']['emoji'])->toBe('😀🎉🚀');
    });

    test('handles special characters', function () {
        $blocks = [
            [
                'type' => 'special',
                'data' => [
                    'quotes' => '"quoted" and \'single\'',
                    'html' => '<script>alert("xss")</script>',
                    'backslash' => 'path\\to\\file',
                    'newline' => "line1\nline2",
                ],
            ],
        ];

        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $blocks,
        ]);

        $fresh = $model->fresh();
        expect($fresh->content_blocks[0]['data']['quotes'])->toBe('"quoted" and \'single\'');
        expect($fresh->content_blocks[0]['data']['html'])->toBe('<script>alert("xss")</script>');
    });

    test('multiple operations in sequence', function () {
        $model = TestBlocksModel::create(['name' => 'Test', 'content_blocks' => null]);

        $model->addBlock(['type' => 'a', 'data' => []]);
        $model->addBlock(['type' => 'b', 'data' => []]);
        $model->addBlock(['type' => 'c', 'data' => []]);
        $model->removeBlock(1); // Remove 'b'
        $model->reorderBlocks([1, 0]); // Swap 'a' and 'c'
        $model->addBlock(['type' => 'd', 'data' => []]);

        expect($model->content_blocks)->toHaveCount(3);
        expect($model->content_blocks[0]['type'])->toBe('c');
        expect($model->content_blocks[1]['type'])->toBe('a');
        expect($model->content_blocks[2]['type'])->toBe('d');
    });

    test('handles missing type field', function () {
        $blocks = [['data' => ['heading' => 'Test']]];

        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $blocks,
        ]);

        // Trait doesn't validate, just stores
        $fresh = $model->fresh();
        expect($fresh->content_blocks[0])->toBe(['data' => ['heading' => 'Test']]);
    });

    test('handles invalid block types', function () {
        $blocks = [['type' => 'nonexistent_type', 'data' => []]];

        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $blocks,
        ]);

        // Trait doesn't validate, just stores
        $fresh = $model->fresh();
        expect($fresh->content_blocks[0]['type'])->toBe('nonexistent_type');
    });

    test('handles wrong data types in fields', function () {
        $blocks = [
            [
                'type' => 'hero',
                'data' => [
                    'heading' => 12345, // Should be string
                    'count' => 'not a number', // Should be int
                ],
            ],
        ];

        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $blocks,
        ]);

        // Trait doesn't validate, just stores
        $fresh = $model->fresh();
        expect($fresh->content_blocks[0]['data']['heading'])->toBe(12345);
        expect($fresh->content_blocks[0]['data']['count'])->toBe('not a number');
    });

    test('handles numeric precision', function () {
        $blocks = [
            [
                'type' => 'numeric',
                'data' => [
                    'float' => 3.141592653589793,
                    'large' => 9007199254740991,
                    'negative' => -42,
                    'zero' => 0,
                ],
            ],
        ];

        $model = TestBlocksModel::create([
            'name' => 'Test',
            'content_blocks' => $blocks,
        ]);

        $fresh = $model->fresh();
        expect($fresh->content_blocks[0]['data']['float'])->toBe(3.141592653589793);
        expect($fresh->content_blocks[0]['data']['large'])->toBe(9007199254740991);
    });
});
