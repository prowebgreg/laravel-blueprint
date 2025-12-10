<?php

declare(strict_types=1);

namespace App\Actions\Media;

use DOMDocument;
use DOMElement;
use DOMXPath;
use enshrined\svgSanitize\Sanitizer;
use Exception;

/**
 * Sanitizes SVG content by removing XSS vectors.
 *
 * Removes:
 * - Script tags and event handlers
 * - External URL references (http://, https://, ftp://)
 * - Dangerous data URIs (data:text/html)
 * - Malicious processing instructions
 *
 * Uses enshrined/svg-sanitize library for primary sanitization.
 */
class SanitizeSvgAction
{
    /**
     * Sanitize SVG content by removing scripts, event handlers, and external references.
     *
     * @throws Exception When input is invalid (empty, whitespace-only, malformed XML, or non-SVG content)
     */
    public function execute(string $svg): string
    {
        // Validate input is not empty or whitespace-only
        if (trim($svg) === '') {
            throw new Exception('SVG content cannot be empty or whitespace-only');
        }

        // Pre-process: remove dangerous data URIs that might break XML parsing
        $svg = $this->removeDangerousDataUris($svg);

        // Initialize the sanitizer
        $sanitizer = new Sanitizer;

        // Remove remote references wrapped in url() (CSS-style)
        $sanitizer->removeRemoteReferences(true);

        // Sanitize the SVG - this also validates the XML
        $sanitized = $sanitizer->sanitize($svg);

        // If sanitization failed, check if it's due to XML errors
        if ($sanitized === false || $sanitized === '' || $sanitized === null) {
            $issues = $sanitizer->getXmlIssues();

            // Check if there were XML parsing errors
            if (! empty($issues)) {
                throw new Exception('Invalid or malformed XML content');
            }

            throw new Exception('SVG sanitization failed');
        }

        // Additional sanitization: remove direct external URL references
        // The library only removes url() wrapped references, not plain HTTP URLs
        $sanitized = $this->removeExternalReferences($sanitized);

        // Verify the result is a valid SVG document
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $loaded = $dom->loadXML($sanitized, LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        if (! $loaded || $dom->documentElement === null || $dom->documentElement->localName !== 'svg') {
            throw new Exception('Content must be a valid SVG document with <svg> root element');
        }

        return $sanitized;
    }

    /**
     * Remove attributes containing external URL references.
     * This handles direct URLs that the library doesn't catch.
     */
    private function removeExternalReferences(string $svg): string
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $dom->loadXML($svg, LIBXML_NONET);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('svg', 'http://www.w3.org/2000/svg');
        $xpath->registerNamespace('xlink', 'http://www.w3.org/1999/xlink');

        // Attributes that might contain external references
        $urlAttributes = ['href', 'xlink:href', 'src', 'data'];

        foreach ($urlAttributes as $attrName) {
            $elements = $xpath->query("//*[@{$attrName}]");

            if ($elements === false) {
                continue;
            }

            foreach ($elements as $element) {
                if (! $element instanceof DOMElement) {
                    continue;
                }

                $attrValue = $element->getAttribute($attrName);

                // Check if it's an external URL (http://, https://, ftp://, //)
                if ($this->isExternalUrl($attrValue)) {
                    $element->removeAttribute($attrName);
                }
            }
        }

        $result = $dom->saveXML();

        // If DOM serialization fails, return original (already validated by library)
        if ($result === false) {
            return $svg;
        }

        return $result;
    }

    /**
     * Check if a URL is external (not a fragment identifier or data URI).
     */
    private function isExternalUrl(string $url): bool
    {
        $url = trim($url);

        // Empty or fragment-only URLs are safe
        if ($url === '' || str_starts_with($url, '#')) {
            return false;
        }

        // Data URIs starting with image/svg are generally safe, others are not
        if (str_starts_with($url, 'data:')) {
            // Block dangerous data URIs like data:text/html
            if (str_starts_with($url, 'data:text/html')) {
                return true;
            }

            // Allow data:image/ URIs (though they might be blocked elsewhere)
            return false;
        }

        // Check for protocol-based URLs
        if (preg_match('~^(https?|ftp|file):~i', $url)) {
            return true;
        }

        // Check for protocol-relative URLs
        if (str_starts_with($url, '//')) {
            return true;
        }

        return false;
    }

    /**
     * Remove dangerous data URIs that contain HTML/XML and would break XML parsing.
     * This is a pre-processing step before the main sanitization.
     */
    private function removeDangerousDataUris(string $svg): string
    {
        // Simple string replacement to remove data:text/html URIs
        // These can contain unescaped < > characters that break XML parsing
        // We look for the pattern and remove everything until we find the closing quote

        $patterns = [
            '/href\s*=\s*"data:text\/html[^"]*"/i',
            "/href\s*=\s*'data:text\/html[^']*'/i",
            '/xlink:href\s*=\s*"data:text\/html[^"]*"/i',
            "/xlink:href\s*=\s*'data:text\/html[^']*'/i",
            '/src\s*=\s*"data:text\/html[^"]*"/i',
            "/src\s*=\s*'data:text\/html[^']*'/i",
        ];

        foreach ($patterns as $pattern) {
            // Replace with empty string to remove the attribute entirely
            $result = preg_replace($pattern, '', $svg);

            // If preg_replace failed, continue with unmodified svg
            if ($result === null) {
                continue;
            }
            $svg = $result;
        }

        return $svg;
    }
}
