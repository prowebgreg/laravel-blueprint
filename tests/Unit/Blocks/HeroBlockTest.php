<?php

declare(strict_types=1);

use App\Blocks\HeroBlock;

test('returns hero as type', function () {
    expect(HeroBlock::type())->toBe('hero');
});

test('schema returns correct field-type mapping with 4 fields', function () {
    $schema = HeroBlock::schema();

    expect($schema)
        ->toBeArray()
        ->toHaveCount(4)
        ->toHaveKeys(['heading', 'lede', 'image', 'cta_button_text'])
        ->and($schema['heading'])->toBe('string')
        ->and($schema['lede'])->toBe('string')
        ->and($schema['image'])->toBe('string')
        ->and($schema['cta_button_text'])->toBe('string');
});

test('validate passes for valid data with all string fields', function () {
    $validData = [
        'heading' => 'Welcome to Our Site',
        'lede' => 'We help businesses grow online.',
        'image' => 'https://cdn.example.com/hero.webp',
        'cta_button_text' => 'Get Started',
    ];

    $failCalled = false;
    $fail = function (string $field, string $message) use (&$failCalled) {
        $failCalled = true;
    };

    HeroBlock::validate($validData, $fail, 0);

    expect($failCalled)->toBeFalse();
});

test('validate passes for empty data', function () {
    $emptyData = [];

    $failCalled = false;
    $fail = function (string $field, string $message) use (&$failCalled) {
        $failCalled = true;
    };

    HeroBlock::validate($emptyData, $fail, 0);

    expect($failCalled)->toBeFalse();
});

test('validate passes for partial data with some fields provided', function () {
    $partialData = [
        'heading' => 'Welcome',
        'cta_button_text' => 'Click Here',
    ];

    $failCalled = false;
    $fail = function (string $field, string $message) use (&$failCalled) {
        $failCalled = true;
    };

    HeroBlock::validate($partialData, $fail, 0);

    expect($failCalled)->toBeFalse();
});

test('validate fails for wrong data type', function () {
    $invalidData = [
        'heading' => 'Valid String',
        'lede' => 12345, // Integer instead of string
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    HeroBlock::validate($invalidData, $fail, 0);

    expect($errors)
        ->toHaveKey('content_blocks.0.data.lede')
        ->and($errors['content_blocks.0.data.lede'])->toContain('must be a string')
        ->and($errors['content_blocks.0.data.lede'])->toContain('int given');
});

test('validate fails for multiple wrong data types', function () {
    $invalidData = [
        'heading' => 123,
        'lede' => true,
        'image' => ['not', 'a', 'string'],
        'cta_button_text' => 45.67,
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    HeroBlock::validate($invalidData, $fail, 0);

    expect($errors)
        ->toHaveKey('content_blocks.0.data.heading')
        ->toHaveKey('content_blocks.0.data.lede')
        ->toHaveKey('content_blocks.0.data.image')
        ->toHaveKey('content_blocks.0.data.cta_button_text')
        ->and($errors['content_blocks.0.data.heading'])->toContain('must be a string')
        ->and($errors['content_blocks.0.data.lede'])->toContain('must be a string')
        ->and($errors['content_blocks.0.data.image'])->toContain('must be a string')
        ->and($errors['content_blocks.0.data.cta_button_text'])->toContain('must be a string');
});

test('validate fails for unknown fields', function () {
    $invalidData = [
        'heading' => 'Valid Heading',
        'unknown_field' => 'This field is not in schema',
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    HeroBlock::validate($invalidData, $fail, 0);

    expect($errors)
        ->toHaveKey('content_blocks.0.data.unknown_field')
        ->and($errors['content_blocks.0.data.unknown_field'])->toBe("Unknown field 'unknown_field' for hero block.");
});

test('validate fails for multiple unknown fields', function () {
    $invalidData = [
        'heading' => 'Valid Heading',
        'unknown_field_1' => 'Unknown 1',
        'unknown_field_2' => 'Unknown 2',
        'another_bad_field' => 'Unknown 3',
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    HeroBlock::validate($invalidData, $fail, 0);

    expect($errors)
        ->toHaveKey('content_blocks.0.data.unknown_field_1')
        ->toHaveKey('content_blocks.0.data.unknown_field_2')
        ->toHaveKey('content_blocks.0.data.another_bad_field')
        ->and($errors['content_blocks.0.data.unknown_field_1'])->toContain("Unknown field 'unknown_field_1'")
        ->and($errors['content_blocks.0.data.unknown_field_2'])->toContain("Unknown field 'unknown_field_2'")
        ->and($errors['content_blocks.0.data.another_bad_field'])->toContain("Unknown field 'another_bad_field'");
});

test('error messages are formatted correctly with block index', function () {
    $invalidData = [
        'heading' => 123,
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    // Test with index 0
    HeroBlock::validate($invalidData, $fail, 0);

    expect($errors)->toHaveKey('content_blocks.0.data.heading');

    // Test with index 5
    $errors = [];
    HeroBlock::validate($invalidData, $fail, 5);

    expect($errors)->toHaveKey('content_blocks.5.data.heading');
});

test('validate handles mixed valid and invalid data', function () {
    $mixedData = [
        'heading' => 'Valid String',
        'lede' => false, // Invalid type
        'image' => 'https://example.com/image.jpg', // Valid
        'unknown' => 'value', // Unknown field
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    HeroBlock::validate($mixedData, $fail, 0);

    expect($errors)
        ->toHaveKey('content_blocks.0.data.lede')
        ->toHaveKey('content_blocks.0.data.unknown')
        ->toHaveCount(2);
});

test('validate accepts null values as invalid type', function () {
    $dataWithNull = [
        'heading' => null,
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    HeroBlock::validate($dataWithNull, $fail, 0);

    expect($errors)
        ->toHaveKey('content_blocks.0.data.heading')
        ->and($errors['content_blocks.0.data.heading'])->toContain('must be a string');
});

test('validate accepts empty strings as valid', function () {
    $dataWithEmptyStrings = [
        'heading' => '',
        'lede' => '',
        'image' => '',
        'cta_button_text' => '',
    ];

    $failCalled = false;
    $fail = function (string $field, string $message) use (&$failCalled) {
        $failCalled = true;
    };

    HeroBlock::validate($dataWithEmptyStrings, $fail, 0);

    expect($failCalled)->toBeFalse();
});
