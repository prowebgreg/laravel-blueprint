<?php

declare(strict_types=1);

namespace App\Actions\Media;

class SanitizeFilenameAction
{
    /**
     * Sanitize a filename by:
     * - Converting to lowercase
     * - Converting spaces/tabs/newlines to hyphens
     * - Removing special characters
     * - Consolidating multiple hyphens
     * - Appending unique 8-char alphanumeric nanoid
     * - Preserving and lowercasing extension
     */
    public function execute(string $filename): string
    {
        // Trim leading/trailing whitespace
        $filename = trim($filename);

        // Handle empty filename
        if ($filename === '') {
            return $this->generateNanoid();
        }

        // Extract extension and basename
        $extension = '';
        $basename = $filename;
        $isDotfileWithExtensionOnly = false;

        // Handle dotfiles first (files starting with .)
        if (str_starts_with($filename, '.')) {
            // Remove leading dot
            $filename = substr($filename, 1);

            if ($filename === '') {
                // Just a dot, return nanoid
                return $this->generateNanoid();
            }

            // Check if this is a dotfile like .jpg (extension only) vs .htaccess or .htaccess.bak
            if (! str_contains($filename, '.')) {
                // Check if this looks like just an extension (common image/video extensions)
                $commonExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp4', 'webm', 'mov', 'avi'];
                if (in_array(strtolower($filename), $commonExtensions, true)) {
                    // This is .ext format - no basename, just extension
                    $isDotfileWithExtensionOnly = true;
                    $extension = $filename;
                    $basename = '';
                }
                // Otherwise treat it as a hidden file name (like .htaccess → basename=htaccess)
            }
        }

        if (! $isDotfileWithExtensionOnly && str_contains($filename, '.')) {
            // Check for compound extensions like .tar.gz
            $compoundExtensions = ['tar.gz', 'tar.bz2', 'tar.xz'];
            $foundCompound = false;

            foreach ($compoundExtensions as $compExt) {
                if (str_ends_with(strtolower($filename), '.'.$compExt)) {
                    // For compound extension, use just the final part (.gz)
                    // so the basename doesn't include "tar" which would be converted to hyphen
                    $extension = substr($compExt, strpos($compExt, '.') + 1); // "gz" from "tar.gz"
                    $basename = substr($filename, 0, -(strlen($compExt) + 1)); // Remove ".tar.gz"
                    $foundCompound = true;
                    break;
                }
            }

            if (! $foundCompound) {
                $lastDotPosition = strrpos($filename, '.');
                $extension = substr($filename, $lastDotPosition + 1);
                $basename = substr($filename, 0, $lastDotPosition);
            }
        } elseif (! $isDotfileWithExtensionOnly) {
            $basename = $filename;
        }

        // Convert to lowercase
        $basename = mb_strtolower($basename, 'UTF-8');

        // Convert dots in basename to hyphens (for files like backup.tar -> backup-tar)
        $basename = str_replace('.', '-', $basename);

        // Remove path separators (/ and \)
        $basename = str_replace(['/', '\\'], '', $basename);

        // Convert whitespace (spaces, tabs, newlines) to hyphens
        $basename = preg_replace('/[\s\t\n\r]+/', '-', $basename);

        // Remove accented characters and unicode (transliterate or remove)
        $basename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $basename);

        // Remove special characters, keep only alphanumeric, hyphens, and underscores
        $basename = preg_replace('/[^a-z0-9_-]/', '', $basename);

        // Consolidate multiple consecutive hyphens into single hyphen
        $basename = preg_replace('/-+/', '-', $basename);

        // Remove leading/trailing hyphens
        $basename = trim($basename, '-');

        // If basename is empty after sanitization, generate random name
        if ($basename === '') {
            $basename = $this->generateNanoid();
        } else {
            // Append nanoid suffix
            $basename = $basename.'-'.$this->generateNanoid();
        }

        // Lowercase extension and append if exists
        if ($extension !== '') {
            $extension = mb_strtolower($extension, 'UTF-8');

            return $basename.'.'.$extension;
        }

        return $basename;
    }

    /**
     * Generate an 8-character lowercase alphanumeric random string
     */
    private function generateNanoid(): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $nanoid = '';

        $randomBytes = random_bytes(8);

        for ($i = 0; $i < 8; $i++) {
            $nanoid .= $characters[ord($randomBytes[$i]) % $charactersLength];
        }

        return $nanoid;
    }
}
