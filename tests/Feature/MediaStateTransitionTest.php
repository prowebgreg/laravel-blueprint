<?php

declare(strict_types=1);

/**
 * Feature Tests for Media State Transitions
 *
 * Tests MediaState enum and state machine behavior:
 * - Valid transitions: uploading → processing → ready
 * - Valid transitions: uploading/processing → failed
 * - Enum helper methods: isAccessible(), isFailed(), isProcessing()
 * - Error message handling
 *
 * @see /specs/003-media-engine/data-model.md (State Transitions section)
 */

use App\Enums\MediaState;
use App\Models\MediaAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MediaState enum helper methods', function () {
    it('isAccessible returns true only for Ready state', function () {
        expect(MediaState::Ready->isAccessible())->toBeTrue()
            ->and(MediaState::Uploading->isAccessible())->toBeFalse()
            ->and(MediaState::Processing->isAccessible())->toBeFalse()
            ->and(MediaState::Failed->isAccessible())->toBeFalse();
    });

    it('isFailed returns true only for Failed state', function () {
        expect(MediaState::Failed->isFailed())->toBeTrue()
            ->and(MediaState::Ready->isFailed())->toBeFalse()
            ->and(MediaState::Uploading->isFailed())->toBeFalse()
            ->and(MediaState::Processing->isFailed())->toBeFalse();
    });

    it('isProcessing returns true for Uploading and Processing states', function () {
        expect(MediaState::Uploading->isProcessing())->toBeTrue()
            ->and(MediaState::Processing->isProcessing())->toBeTrue()
            ->and(MediaState::Ready->isProcessing())->toBeFalse()
            ->and(MediaState::Failed->isProcessing())->toBeFalse();
    });
});

describe('valid state transitions', function () {
    it('transitions from uploading to processing', function () {
        $asset = MediaAsset::factory()->uploading()->create();

        expect($asset->state)->toBe(MediaState::Uploading);

        $asset->state = MediaState::Processing;
        $asset->save();
        $asset->refresh();

        expect($asset->state)->toBe(MediaState::Processing)
            ->and($asset->state->isProcessing())->toBeTrue();
    });

    it('transitions from processing to ready', function () {
        $asset = MediaAsset::factory()->processing()->create();

        expect($asset->state)->toBe(MediaState::Processing);

        $asset->state = MediaState::Ready;
        $asset->error_message = null;
        $asset->save();
        $asset->refresh();

        expect($asset->state)->toBe(MediaState::Ready)
            ->and($asset->state->isAccessible())->toBeTrue()
            ->and($asset->error_message)->toBeNull();
    });

    it('transitions from uploading to failed with error message', function () {
        $asset = MediaAsset::factory()->uploading()->create();

        expect($asset->state)->toBe(MediaState::Uploading);

        $asset->state = MediaState::Failed;
        $asset->error_message = 'S3 upload failed after retries';
        $asset->save();
        $asset->refresh();

        expect($asset->state)->toBe(MediaState::Failed)
            ->and($asset->state->isFailed())->toBeTrue()
            ->and($asset->error_message)->toBe('S3 upload failed after retries');
    });

    it('transitions from processing to failed with error message', function () {
        $asset = MediaAsset::factory()->processing()->create();

        expect($asset->state)->toBe(MediaState::Processing);

        $asset->state = MediaState::Failed;
        $asset->error_message = 'Variant generation failed: timeout';
        $asset->save();
        $asset->refresh();

        expect($asset->state)->toBe(MediaState::Failed)
            ->and($asset->state->isFailed())->toBeTrue()
            ->and($asset->error_message)->toBe('Variant generation failed: timeout');
    });
});

describe('state transition business logic', function () {
    it('allows ready to processing transition for reprocessing scenario', function () {
        // Reprocessing may be valid for regenerating variants
        $asset = MediaAsset::factory()->create([
            'state' => MediaState::Ready,
        ]);

        expect($asset->state)->toBe(MediaState::Ready);

        $asset->state = MediaState::Processing;
        $asset->save();
        $asset->refresh();

        expect($asset->state)->toBe(MediaState::Processing);
    });

    it('allows failed to processing transition for retry scenario', function () {
        // Manual retry of failed assets should be allowed
        $asset = MediaAsset::factory()->failed()->create();

        expect($asset->state)->toBe(MediaState::Failed);

        $asset->state = MediaState::Processing;
        $asset->error_message = null;
        $asset->save();
        $asset->refresh();

        expect($asset->state)->toBe(MediaState::Processing)
            ->and($asset->error_message)->toBeNull();
    });
});

describe('state persistence and factory states', function () {
    it('persists uploading state from factory', function () {
        $asset = MediaAsset::factory()->uploading()->create();

        expect($asset->state)->toBe(MediaState::Uploading)
            ->and($asset->state->isProcessing())->toBeTrue()
            ->and($asset->state->isAccessible())->toBeFalse();
    });

    it('persists processing state from factory', function () {
        $asset = MediaAsset::factory()->processing()->create();

        expect($asset->state)->toBe(MediaState::Processing)
            ->and($asset->state->isProcessing())->toBeTrue()
            ->and($asset->state->isAccessible())->toBeFalse();
    });

    it('persists ready state from factory as default', function () {
        $asset = MediaAsset::factory()->create();

        expect($asset->state)->toBe(MediaState::Ready)
            ->and($asset->state->isAccessible())->toBeTrue()
            ->and($asset->state->isProcessing())->toBeFalse()
            ->and($asset->state->isFailed())->toBeFalse();
    });

    it('persists failed state from factory with error message', function () {
        $asset = MediaAsset::factory()->failed()->create();

        expect($asset->state)->toBe(MediaState::Failed)
            ->and($asset->state->isFailed())->toBeTrue()
            ->and($asset->state->isAccessible())->toBeFalse()
            ->and($asset->error_message)->not->toBeNull();
    });
});

describe('error message handling', function () {
    it('stores error message when transitioning to failed state', function () {
        $asset = MediaAsset::factory()->uploading()->create();

        $errorMessage = 'Connection timeout during upload to S3';
        $asset->state = MediaState::Failed;
        $asset->error_message = $errorMessage;
        $asset->save();
        $asset->refresh();

        expect($asset->state)->toBe(MediaState::Failed)
            ->and($asset->error_message)->toBe($errorMessage);
    });

    it('clears error message when transitioning from failed to processing', function () {
        $asset = MediaAsset::factory()->failed()->create([
            'error_message' => 'Previous error message',
        ]);

        expect($asset->error_message)->not->toBeNull();

        $asset->state = MediaState::Processing;
        $asset->error_message = null;
        $asset->save();
        $asset->refresh();

        expect($asset->state)->toBe(MediaState::Processing)
            ->and($asset->error_message)->toBeNull();
    });

    it('clears error message when transitioning to ready state', function () {
        $asset = MediaAsset::factory()->processing()->create([
            'error_message' => 'Some previous error',
        ]);

        $asset->state = MediaState::Ready;
        $asset->error_message = null;
        $asset->save();
        $asset->refresh();

        expect($asset->state)->toBe(MediaState::Ready)
            ->and($asset->error_message)->toBeNull();
    });

    it('stores detailed error message with stack trace info', function () {
        $asset = MediaAsset::factory()->processing()->create();

        $detailedError = "Variant generation failed:\nFile: GenerateVariantsAction.php:142\nMessage: GD library error";
        $asset->state = MediaState::Failed;
        $asset->error_message = $detailedError;
        $asset->save();
        $asset->refresh();

        expect($asset->error_message)->toContain('Variant generation failed')
            ->and($asset->error_message)->toContain('GenerateVariantsAction.php');
    });
});

describe('complete state transition workflows', function () {
    it('completes successful upload workflow: uploading -> processing -> ready', function () {
        // Step 1: Create asset in uploading state
        $asset = MediaAsset::factory()->uploading()->create();
        expect($asset->state)->toBe(MediaState::Uploading)
            ->and($asset->state->isProcessing())->toBeTrue();

        // Step 2: Transition to processing
        $asset->state = MediaState::Processing;
        $asset->save();
        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Processing)
            ->and($asset->state->isProcessing())->toBeTrue();

        // Step 3: Transition to ready
        $asset->state = MediaState::Ready;
        $asset->error_message = null;
        $asset->save();
        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Ready)
            ->and($asset->state->isAccessible())->toBeTrue()
            ->and($asset->state->isProcessing())->toBeFalse();
    });

    it('completes failed upload workflow: uploading -> failed', function () {
        // Step 1: Create asset in uploading state
        $asset = MediaAsset::factory()->uploading()->create();
        expect($asset->state)->toBe(MediaState::Uploading);

        // Step 2: Transition directly to failed (S3 upload error)
        $asset->state = MediaState::Failed;
        $asset->error_message = 'Upload to S3 failed after 3 retries';
        $asset->save();
        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Failed)
            ->and($asset->state->isFailed())->toBeTrue()
            ->and($asset->error_message)->not->toBeNull();
    });

    it('completes failed processing workflow: uploading -> processing -> failed', function () {
        // Step 1: Create asset in uploading state
        $asset = MediaAsset::factory()->uploading()->create();
        expect($asset->state)->toBe(MediaState::Uploading);

        // Step 2: Transition to processing
        $asset->state = MediaState::Processing;
        $asset->save();
        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Processing);

        // Step 3: Transition to failed (variant generation error)
        $asset->state = MediaState::Failed;
        $asset->error_message = 'Variant generation timeout after 180s';
        $asset->save();
        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Failed)
            ->and($asset->state->isFailed())->toBeTrue()
            ->and($asset->error_message)->toBe('Variant generation timeout after 180s');
    });

    it('completes retry workflow: failed -> processing -> ready', function () {
        // Step 1: Create failed asset
        $asset = MediaAsset::factory()->failed()->create([
            'error_message' => 'Initial processing error',
        ]);
        expect($asset->state)->toBe(MediaState::Failed);

        // Step 2: Retry - transition to processing
        $asset->state = MediaState::Processing;
        $asset->error_message = null;
        $asset->save();
        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Processing)
            ->and($asset->error_message)->toBeNull();

        // Step 3: Success - transition to ready
        $asset->state = MediaState::Ready;
        $asset->save();
        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Ready)
            ->and($asset->state->isAccessible())->toBeTrue();
    });
});

describe('state queries', function () {
    it('can query assets by state', function () {
        MediaAsset::factory()->uploading()->count(2)->create();
        MediaAsset::factory()->processing()->count(3)->create();
        MediaAsset::factory()->create(); // Ready (default)
        MediaAsset::factory()->failed()->count(1)->create();

        expect(MediaAsset::where('state', MediaState::Uploading)->count())->toBe(2)
            ->and(MediaAsset::where('state', MediaState::Processing)->count())->toBe(3)
            ->and(MediaAsset::where('state', MediaState::Ready)->count())->toBe(1)
            ->and(MediaAsset::where('state', MediaState::Failed)->count())->toBe(1);
    });

    it('can query assets in processing states', function () {
        MediaAsset::factory()->uploading()->count(2)->create();
        MediaAsset::factory()->processing()->count(3)->create();
        MediaAsset::factory()->create(); // Ready
        MediaAsset::factory()->failed()->create();

        $processingAssets = MediaAsset::whereIn('state', [
            MediaState::Uploading,
            MediaState::Processing,
        ])->get();

        expect($processingAssets)->toHaveCount(5);
        foreach ($processingAssets as $asset) {
            expect($asset->state->isProcessing())->toBeTrue();
        }
    });

    it('can query accessible assets', function () {
        MediaAsset::factory()->uploading()->create();
        MediaAsset::factory()->processing()->create();
        MediaAsset::factory()->count(3)->create(); // Ready
        MediaAsset::factory()->failed()->create();

        $accessibleAssets = MediaAsset::where('state', MediaState::Ready)->get();

        expect($accessibleAssets)->toHaveCount(3);
        foreach ($accessibleAssets as $asset) {
            expect($asset->state->isAccessible())->toBeTrue();
        }
    });
});
