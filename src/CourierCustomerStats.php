<?php

namespace Ham\BdCourier\CustomerDeliveryStats;

use Ham\BdCourier\CustomerDeliveryStats\Services\PathaoService;
use Ham\BdCourier\CustomerDeliveryStats\Services\SteadfastService;
use Ham\BdCourier\CustomerDeliveryStats\Services\RedXService;
use Ham\BdCourier\CustomerDeliveryStats\Config\ConfigManager;

/**
 * Courier Customer Stats Main Class
 * 
 * Main entry point for the BD Courier Customer Delivery Stats package.
 * Provides a unified interface to check customer statistics across multiple courier services.
 */
class CourierCustomerStats
{
    protected $config;
    protected $pathaoService;
    protected $steadfastService;
    protected $redxService;

    /**
     * Constructor
     * 
     * @param array|null $config Optional configuration array
     */
    public function __construct(?array $config = null)
    {
        $this->config = new ConfigManager($config);
        
        // Initialize services only when needed (lazy loading)
    }

    /**
     * Check customer delivery history across all available courier services
     * 
     * @param string $phoneNumber Customer phone number
     * @return array Results from all courier services
     */
    public function check(string $phoneNumber): array
    {
        $results = [];
        
        // Try to check with Pathao
        try {
            $results['pathao'] = $this->checkPathao($phoneNumber);
        } catch (\Exception $e) {
            $results['pathao'] = [
                'error' => $e->getMessage(),
                'success' => 0,
                'cancel' => 0,
                'total' => 0
            ];
        }
        
        // Try to check with Steadfast
        try {
            $results['steadfast'] = $this->checkSteadfast($phoneNumber);
        } catch (\Exception $e) {
            $results['steadfast'] = [
                'error' => $e->getMessage(),
                'success' => 0,
                'cancel' => 0,
                'total' => 0
            ];
        }
        
        // Try to check with RedX
        try {
            $results['redx'] = $this->checkRedX($phoneNumber);
        } catch (\Exception $e) {
            $results['redx'] = [
                'error' => $e->getMessage(),
                'success' => 0,
                'cancel' => 0,
                'total' => 0
            ];
        }
        
        return $results;
    }

    /**
     * Check customer delivery history with Pathao only
     * 
     * @param string $phoneNumber Customer phone number
     * @return array Delivery statistics
     */
    public function checkPathao(string $phoneNumber): array
    {
        if (!$this->pathaoService) {
            $this->pathaoService = new PathaoService($this->config);
        }
        
        return $this->pathaoService->check($phoneNumber);
    }

    /**
     * Check customer delivery history with Steadfast only
     * 
     * @param string $phoneNumber Customer phone number
     * @return array Delivery statistics
     */
    public function checkSteadfast(string $phoneNumber): array
    {
        if (!$this->steadfastService) {
            $this->steadfastService = new SteadfastService($this->config);
        }
        
        return $this->steadfastService->check($phoneNumber);
    }
    
    /**
     * Check customer delivery history with RedX only
     * 
     * @param string $phoneNumber Customer phone number
     * @return array Delivery statistics
     */
    public function checkRedX(string $phoneNumber): array
    {
        if (!$this->redxService) {
            $this->redxService = new RedXService($this->config);
        }
        
        return $this->redxService->check($phoneNumber);
    }

    /**
     * Set configuration
     * 
     * @param array $config Configuration array
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config->setConfig($config);
        return $this;
    }

    /**
     * Get current configuration
     * 
     * @return array Current configuration
     */
    public function getConfig(): array
    {
        return $this->config->getAll();
    }
} 