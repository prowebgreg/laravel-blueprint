<?php

declare(strict_types=1);

use App\Blocks\CtaBlock;

it('returns cta as type', function () {
    expect(CtaBlock::type())->toBe('cta');
});

it('returns correct schema with all string fields', function () {
    $schema = CtaBlock::schema();

    expect($schema)->toBeArray()
        ->toHaveCount(3)
        ->toHaveKey('heading', 'string')
        ->toHaveKey('lede', 'string')
        ->toHaveKey('cta_button_text', 'string');
});

it('validates successfully with all valid fields', function () {
    $data = [
        'heading' => 'Get Started Today',
        'lede' => 'Join thousands of satisfied customers',
        'cta_button_text' => 'Sign Up Now',
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 0);

    expect($errors)->toBeEmpty();
});

it('validates successfully with empty data', function () {
    $data = [];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 0);

    expect($errors)->toBeEmpty();
});

it('validates successfully with partial data', function () {
    $data = [
        'heading' => 'Special Offer',
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 0);

    expect($errors)->toBeEmpty();
});

it('validates successfully with only lede field', function () {
    $data = [
        'lede' => 'Limited time offer available now',
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 1);

    expect($errors)->toBeEmpty();
});

it('validates successfully with only cta_button_text field', function () {
    $data = [
        'cta_button_text' => 'Learn More',
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 2);

    expect($errors)->toBeEmpty();
});

it('fails validation when heading is not a string', function () {
    $data = [
        'heading' => 123,
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 0);

    expect($errors)->toHaveKey('content_blocks.0.data.heading')
        ->and($errors['content_blocks.0.data.heading'])->toBe('The heading field must be a string, int given.');
});

it('fails validation when lede is not a string', function () {
    $data = [
        'lede' => ['array', 'value'],
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 1);

    expect($errors)->toHaveKey('content_blocks.1.data.lede')
        ->and($errors['content_blocks.1.data.lede'])->toBe('The lede field must be a string, array given.');
});

it('fails validation when cta_button_text is not a string', function () {
    $data = [
        'cta_button_text' => true,
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 2);

    expect($errors)->toHaveKey('content_blocks.2.data.cta_button_text')
        ->and($errors['content_blocks.2.data.cta_button_text'])->toBe('The cta_button_text field must be a string, bool given.');
});

it('fails validation when multiple fields have wrong types', function () {
    $data = [
        'heading' => 456,
        'lede' => null,
        'cta_button_text' => 78.9,
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 0);

    expect($errors)->toHaveCount(3)
        ->toHaveKey('content_blocks.0.data.heading')
        ->toHaveKey('content_blocks.0.data.lede')
        ->toHaveKey('content_blocks.0.data.cta_button_text')
        ->and($errors['content_blocks.0.data.heading'])->toBe('The heading field must be a string, int given.')
        ->and($errors['content_blocks.0.data.lede'])->toBe('The lede field must be a string, null given.')
        ->and($errors['content_blocks.0.data.cta_button_text'])->toBe('The cta_button_text field must be a string, float given.');
});

it('fails validation for unknown field', function () {
    $data = [
        'unknown_field' => 'value',
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 0);

    expect($errors)->toHaveKey('content_blocks.0.data.unknown_field')
        ->and($errors['content_blocks.0.data.unknown_field'])->toBe("Unknown field 'unknown_field' for block type 'cta'.");
});

it('fails validation for multiple unknown fields', function () {
    $data = [
        'invalid_field' => 'test',
        'another_invalid' => 'value',
        'heading' => 'Valid heading',
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 3);

    expect($errors)->toHaveCount(2)
        ->toHaveKey('content_blocks.3.data.invalid_field')
        ->toHaveKey('content_blocks.3.data.another_invalid')
        ->and($errors['content_blocks.3.data.invalid_field'])->toBe("Unknown field 'invalid_field' for block type 'cta'.")
        ->and($errors['content_blocks.3.data.another_invalid'])->toBe("Unknown field 'another_invalid' for block type 'cta'.");
});

it('includes block index in error messages for different positions', function () {
    $data = [
        'heading' => 999,
    ];

    // Test index 0
    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 0);
    expect($errors)->toHaveKey('content_blocks.0.data.heading');

    // Test index 5
    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 5);
    expect($errors)->toHaveKey('content_blocks.5.data.heading');

    // Test index 42
    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 42);
    expect($errors)->toHaveKey('content_blocks.42.data.heading');
});

it('combines unknown fields and type errors correctly', function () {
    $data = [
        'heading' => 123,
        'unknown_field' => 'value',
        'lede' => 'Valid lede text',
    ];

    $errors = [];
    $fail = function (string $field, string $message) use (&$errors) {
        $errors[$field] = $message;
    };

    CtaBlock::validate($data, $fail, 1);

    expect($errors)->toHaveCount(2)
        ->toHaveKey('content_blocks.1.data.heading')
        ->toHaveKey('content_blocks.1.data.unknown_field')
        ->and($errors['content_blocks.1.data.heading'])->toBe('The heading field must be a string, int given.')
        ->and($errors['content_blocks.1.data.unknown_field'])->toBe("Unknown field 'unknown_field' for block type 'cta'.");
});
