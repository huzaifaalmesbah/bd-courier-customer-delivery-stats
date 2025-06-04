<?php

namespace Ham\BdCourier\CustomerDeliveryStats\Services;

use Ham\BdCourier\CustomerDeliveryStats\Http\HttpClient;
use Ham\BdCourier\CustomerDeliveryStats\Helpers\PhoneValidator;
use Ham\BdCourier\CustomerDeliveryStats\Config\ConfigManager;

/**
 * Pathao Courier Service
 * 
 * Service for checking customer delivery history through Pathao courier API.
 */
class PathaoService
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
        $this->httpClient = new HttpClient();
        
        // Validate required configuration
        $this->config->validateRequired(['pathao_user', 'pathao_password']);
    }

    /**
     * Check customer delivery history with Pathao
     * 
     * @param string $phoneNumber Customer phone number
     * @return array Delivery statistics
     * @throws \Exception If API request fails
     */
    public function check(string $phoneNumber): array
    {
        // Validate phone number
        PhoneValidator::validate($phoneNumber);

        // Login to Pathao
        $accessToken = $this->login();
        
        // Get customer delivery statistics
        return $this->getCustomerStats($phoneNumber, $accessToken);
    }

    /**
     * Login to Pathao API and get access token
     * 
     * @return string Access token
     * @throws \Exception If login fails
     */
    protected function login(): string
    {
        $response = $this->httpClient->post('https://merchant.pathao.com/api/v1/login', [
            'username' => $this->config->get('pathao_user'),
            'password' => $this->config->get('pathao_password'),
        ]);

        if (!$response->successful()) {
            throw new \Exception('Pathao login failed: HTTP ' . $response->status());
        }

        $data = $response->json();
        
        if (!isset($data['access_token'])) {
            throw new \Exception('Pathao login failed: No access token received');
        }

        return trim($data['access_token']);
    }

    /**
     * Get customer delivery statistics
     * 
     * @param string $phoneNumber Customer phone number
     * @param string $accessToken API access token
     * @return array Customer delivery statistics
     * @throws \Exception If API request fails
     */
    protected function getCustomerStats(string $phoneNumber, string $accessToken): array
    {
        $response = $this->httpClient
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])
            ->post('https://merchant.pathao.com/api/v1/user/success', [
                'phone' => $phoneNumber,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Pathao customer stats request failed: HTTP ' . $response->status());
        }

        $data = $response->json();
        
        if (!isset($data['data']['customer'])) {
            throw new \Exception('Invalid response format from Pathao API');
        }

        $customer = $data['data']['customer'];
        $successful = $customer['successful_delivery'] ?? 0;
        $total = $customer['total_delivery'] ?? 0;
        $cancelled = $total - $successful;

        return [
            'success' => $successful,
            'cancel' => $cancelled,
            'total' => $total,
        ];
    }
}
