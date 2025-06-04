<?php

namespace Ham\BdCourier\CustomerDeliveryStats\Helpers;

/**
 * Phone Number Validator
 * 
 * Validates Bangladeshi phone numbers without framework dependencies.
 */
class PhoneValidator
{
    /**
     * Validate Bangladeshi phone number
     * 
     * @param string $phoneNumber Phone number to validate
     * @return bool True if valid
     * @throws \InvalidArgumentException If phone number is invalid
     */
    public static function validate(string $phoneNumber): bool
    {
        if (empty($phoneNumber)) {
            throw new \InvalidArgumentException('Phone number is required.');
        }

        // Bangladeshi phone number regex: starts with 01, followed by 3-9, then 8 digits
        $pattern = '/^01[3-9][0-9]{8}$/';
        
        if (!preg_match($pattern, $phoneNumber)) {
            throw new \InvalidArgumentException(
                'Invalid Bangladeshi phone number. Please use the local format (e.g., 01712345678) without the +88 prefix.'
            );
        }

        return true;
    }

    /**
     * Check if phone number is valid without throwing exception
     * 
     * @param string $phoneNumber Phone number to check
     * @return bool True if valid, false otherwise
     */
    public static function isValid(string $phoneNumber): bool
    {
        try {
            return self::validate($phoneNumber);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Sanitize phone number by removing common prefixes and formatting
     * 
     * @param string $phoneNumber Phone number to sanitize
     * @return string Sanitized phone number
     */
    public static function sanitize(string $phoneNumber): string
    {
        // Remove whitespace and common separators
        $cleaned = preg_replace('/[\s\-\(\)\.]+/', '', $phoneNumber);
        
        // Remove +88 country code if present
        $cleaned = preg_replace('/^\+88/', '', $cleaned);
        
        // Remove 88 country code if present at start
        $cleaned = preg_replace('/^88(01)/', '$1', $cleaned);
        
        return $cleaned;
    }

    /**
     * Format phone number to standard Bangladeshi format
     * 
     * @param string $phoneNumber Phone number to format
     * @return string Formatted phone number
     * @throws \InvalidArgumentException If phone number is invalid
     */
    public static function format(string $phoneNumber): string
    {
        $sanitized = self::sanitize($phoneNumber);
        self::validate($sanitized);
        return $sanitized;
    }

    /**
     * Get phone number with country code
     * 
     * @param string $phoneNumber Phone number
     * @return string Phone number with +88 prefix
     * @throws \InvalidArgumentException If phone number is invalid
     */
    public static function withCountryCode(string $phoneNumber): string
    {
        $formatted = self::format($phoneNumber);
        return '+88' . $formatted;
    }

    /**
     * Get validation error message for invalid phone number
     * 
     * @param string $phoneNumber Phone number to check
     * @return string|null Error message or null if valid
     */
    public static function getValidationError(string $phoneNumber): ?string
    {
        try {
            self::validate($phoneNumber);
            return null;
        } catch (\InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }
} 