<?php

namespace App\Helpers;

/**
 * CSP Nonce Helper
 *
 * Generates and manages Content Security Policy nonces for inline scripts and styles.
 * This helps prevent XSS attacks by allowing only specifically tagged inline content.
 */
class CspNonceHelper
{
    private static ?string $nonce = null;

    /**
     * Generate a new nonce if one doesn't exist
     *
     * @return string The generated nonce
     */
    public static function generate(): string
    {
        if (self::$nonce === null) {
            self::$nonce = base64_encode(random_bytes(16));
        }
        return self::$nonce;
    }

    /**
     * Get the current nonce, generating one if needed
     *
     * @return string The nonce
     */
    public static function get(): string
    {
        return self::$nonce ?? self::generate();
    }

    /**
     * Reset the nonce (useful for testing)
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$nonce = null;
    }
}
