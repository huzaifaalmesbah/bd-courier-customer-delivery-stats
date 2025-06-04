<?php

namespace Ham\BdCourier\CustomerDeliveryStats\Services;

use Ham\BdCourier\CustomerDeliveryStats\Http\HttpClient;
use Ham\BdCourier\CustomerDeliveryStats\Helpers\PhoneValidator;
use Ham\BdCourier\CustomerDeliveryStats\Config\ConfigManager;

/**
 * Steadfast Courier Service
 * 
 * Service for checking customer delivery history through Steadfast courier.
 */
class SteadfastService
{
    protected $config;
    protected $httpClient;

    /**
     * Constructor
     * 
     * @param ConfigManager $config Configuration manager
     */
    public function __construct(ConfigManager $config)
    {
        $this->config = $config;
        $this->httpClient = new HttpClient([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept' => 'application/json, text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Connection' => 'keep-alive',
            ]
        ]);
        
        // Validate required configuration
        $this->config->validateRequired(['steadfast_user', 'steadfast_password']);
    }

    /**
     * Check customer delivery history with Steadfast
     * 
     * @param string $phoneNumber Customer phone number
     * @return array Delivery statistics
     * @throws \Exception If request fails
     */
    public function check(string $phoneNumber): array
    {
        // Validate phone number
        PhoneValidator::validate($phoneNumber);

        // Login and get session cookies
        $cookies = $this->login();
        
        // Get customer delivery statistics
        return $this->getCustomerStats($phoneNumber, $cookies);
    }

    /**
     * Login to Steadfast and establish session
     * 
     * @return array Session cookies
     * @throws \Exception If login fails
     */
    protected function login(): array
    {
        // Step 1: Get login page and CSRF token
        $loginPageResponse = $this->httpClient->get('https://steadfast.com.bd/login');
        
        if (!$loginPageResponse->successful()) {
            throw new \Exception('Steadfast login page request failed: HTTP ' . $loginPageResponse->status());
        }

        $token = $this->extractCsrfToken($loginPageResponse->body());
        if (!$token) {
            throw new \Exception('CSRF token not found on Steadfast login page');
        }

        $cookies = $loginPageResponse->cookies();

        // Step 2: Perform login
        $loginResponse = $this->httpClient
            ->withCookies($cookies, 'steadfast.com.bd')
            ->asForm()
            ->post('https://steadfast.com.bd/login', [
                '_token' => $token,
                'email' => $this->config->get('steadfast_user'),
                'password' => $this->config->get('steadfast_password')
            ]);

        if (!($loginResponse->successful() || $loginResponse->redirect())) {
            throw new \Exception('Steadfast login failed: HTTP ' . $loginResponse->status());
        }

        return array_merge($cookies, $loginResponse->cookies());
    }

    /**
     * Get customer delivery statistics
     * 
     * @param string $phoneNumber Customer phone number
     * @param array $cookies Session cookies
     * @return array Customer delivery statistics
     * @throws \Exception If API request fails
     */
    protected function getCustomerStats(string $phoneNumber, array $cookies): array
    {
        // Access fraud check API endpoint directly
        $response = $this->httpClient
            ->withCookies($cookies, 'steadfast.com.bd')
            ->get('https://steadfast.com.bd/user/frauds/check/' . urlencode($phoneNumber));

        if (!$response->successful()) {
            throw new \Exception('Steadfast fraud check request failed: HTTP ' . $response->status());
        }

        // Parse JSON response
        $data = $response->json();
        
        if (!is_array($data)) {
            throw new \Exception('Invalid JSON response from Steadfast API');
        }

        $successful = $data['total_delivered'] ?? 0;
        $cancelled = $data['total_cancelled'] ?? 0;
        $total = $successful + $cancelled;

        return [
            'success' => $successful,
            'cancel' => $cancelled,
            'total' => $total,
        ];
    }

    /**
     * Extract CSRF token from HTML response
     * 
     * @param string $html HTML content
     * @return string|null CSRF token or null if not found
     */
    protected function extractCsrfToken(string $html): ?string
    {
        // Look for CSRF token in hidden input field
        if (preg_match('/<input[^>]*name=["\']_token["\'][^>]*value=["\']([^"\']+)["\']/', $html, $matches)) {
            return $matches[1];
        }

        // Look for CSRF token in meta tag
        if (preg_match('/<meta[^>]*name=["\']csrf-token["\'][^>]*content=["\']([^"\']+)["\']/', $html, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
