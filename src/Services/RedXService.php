<?php

namespace Ham\BdCourier\CustomerDeliveryStats\Services;

use Ham\BdCourier\CustomerDeliveryStats\Helpers\PhoneValidator;
use Ham\BdCourier\CustomerDeliveryStats\Config\ConfigManager;

/**
 * RedX Courier Service
 * 
 * Service for checking customer delivery history through RedX courier API.
 * Based on browser testing, RedX uses session-based authentication.
 */
class RedXService
{
    protected $config;
    protected $accessToken;

    /**
     * Constructor
     * 
     * @param ConfigManager $config Configuration manager
     */
    public function __construct(ConfigManager $config)
    {
        $this->config = $config;
        
        // Validate required configuration
        $this->config->validateRequired(['redx_user', 'redx_password']);
    }

    /**
     * Check customer delivery history with RedX
     * 
     * @param string $phoneNumber Customer phone number
     * @return array Delivery statistics
     * @throws \Exception If API request fails
     */
    public function check(string $phoneNumber): array
    {
        // Validate phone number
        PhoneValidator::validate($phoneNumber);

        // Login to RedX and establish session
        $this->login();
        
        // Get customer delivery statistics
        return $this->getCustomerStats($phoneNumber);
    }

    /**
     * Login to RedX and establish session
     * 
     * Based on browser testing, RedX uses the API endpoint v4/auth/login
     * with phone and password as JSON payload.
     * 
     * @return void
     * @throws \Exception If login fails
     */
    protected function login(): void
    {
        $username = $this->config->get('redx_user');
        $password = $this->config->get('redx_password');
        
        // Format phone number for RedX API (add 88 country code)
        $formattedPhone = $this->formatPhoneForApi($username);
        
        // Prepare login data
        $loginData = json_encode([
            'phone' => $formattedPhone,
            'password' => $password
        ]);

        // Use direct curl command to avoid PHP networking issues
        $command = 'curl -X POST "https://api.redx.com.bd/v4/auth/login" ' .
                   '-H "Content-Type: application/json" ' .
                   '-H "Accept: application/json" ' .
                   '-d \'' . $loginData . '\' ' .
                   '--max-time 30 ' .
                   '--silent';
        
        $response = shell_exec($command);
        
        if (!$response) {
            throw new \Exception('RedX login request failed: No response received');
        }

        // Check if login response contains success data
        $loginResult = json_decode($response, true);
        if (!isset($loginResult['data']) || !isset($loginResult['data']['accessToken'])) {
            throw new \Exception('RedX authentication failed: ' . ($loginResult['message'] ?? 'No access token received'));
        }
        
        // Store access token for subsequent API calls
        $this->accessToken = $loginResult['data']['accessToken'];
    }

    /**
     * Get customer delivery statistics
     * 
     * @param string $phoneNumber Customer phone number
     * @return array Customer delivery statistics
     * @throws \Exception If API request fails
     */
    protected function getCustomerStats(string $phoneNumber): array
    {
        // Ensure phone number has country code format for API
        $formattedPhone = $this->formatPhoneForApi($phoneNumber);
        
        // Make the API request using curl
        $apiUrl = 'https://redx.com.bd/api/redx_se/admin/parcel/customer-success-return-rate?phoneNumber=' . urlencode($formattedPhone);
        
        $command = 'curl "' . $apiUrl . '" ' .
                   '-H "Authorization: Bearer ' . $this->accessToken . '" ' .
                   '-H "Accept: application/json" ' .
                   '--max-time 30 ' .
                   '--silent';
        
        $response = shell_exec($command);
        
        if (!$response) {
            throw new \Exception('RedX customer stats request failed: No response received');
        }

        $data = json_decode($response, true);
        
        if (!isset($data['code']) || $data['code'] !== 200) {
            throw new \Exception('RedX API returned error: ' . ($data['message'] ?? 'Unknown error'));
        }

        if (!isset($data['data'])) {
            throw new \Exception('Invalid response format from RedX API');
        }

        $customerData = $data['data'];
        $total = (int) ($customerData['totalParcels'] ?? 0);
        $delivered = (int) ($customerData['deliveredParcels'] ?? 0);
        $returnPercentage = (float) ($customerData['returnPercentage'] ?? 0);
        $cancelled = (int) round($total * ($returnPercentage / 100));

        return [
            'success' => $delivered,
            'cancel' => $cancelled,
            'total' => $total,
        ];
    }

    /**
     * Format phone number for RedX API (requires country code format)
     * 
     * @param string $phoneNumber Phone number to format
     * @return string Formatted phone number
     */
    protected function formatPhoneForApi(string $phoneNumber): string
    {
        // Remove any existing country code and formatting
        $cleaned = PhoneValidator::sanitize($phoneNumber);
        
        // Remove leading '88' if present
        if (substr($cleaned, 0, 2) === '88') {
            $cleaned = substr($cleaned, 2);
        }
        
        // Add '88' country code (RedX API expects this format)
        return '88' . $cleaned;
    }
}
