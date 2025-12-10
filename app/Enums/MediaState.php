<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaState: string
{
    case Uploading = 'uploading';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';

    /**
     * Check if state allows variant access.
     */
    public function isAccessible(): bool
    {
        return $this === self::Ready;
    }

    /**
     * Check if state indicates a terminal failure.
     */
    public function isFailed(): bool
    {
        return $this === self::Failed;
    }

    /**
     * Check if state indicates processing is in progress.
     */
    public function isProcessing(): bool
    {
        return $this === self::Uploading || $this === self::Processing;
    }
}
