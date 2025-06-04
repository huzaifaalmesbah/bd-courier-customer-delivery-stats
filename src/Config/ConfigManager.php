<?php

namespace Ham\BdCourier\CustomerDeliveryStats\Config;

/**
 * Configuration Manager
 * 
 * Handles configuration for the package, supporting both environment variables
 * and direct configuration arrays. Works with any PHP environment.
 */
class ConfigManager
{
    protected $config = [];
    protected $defaults = [
        'pathao_user' => null,
        'pathao_password' => null,
        'steadfast_user' => null,
        'steadfast_password' => null,
        'redx_user' => null,
        'redx_password' => null,
    ];

    /**
     * Constructor
     * 
     * @param array|null $config Configuration array
     */
    public function __construct(?array $config = [])
    {
        $this->loadDefaults();
        $this->loadFromEnvironment();
        if ($config !== null) {
            $this->setConfig($config);
        }
    }

    /**
     * Set configuration
     * 
     * @param array $config Configuration array
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Get configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Get all configuration
     * 
     * @return array All configuration
     */
    public function getAll(): array
    {
        return $this->config;
    }

    /**
     * Check if configuration key exists
     * 
     * @param string $key Configuration key
     * @return bool True if key exists
     */
    public function has(string $key): bool
    {
        return isset($this->config[$key]);
    }

    /**
     * Load default configuration
     */
    protected function loadDefaults(): void
    {
        $this->config = $this->defaults;
    }

    /**
     * Load configuration from environment variables
     */
    protected function loadFromEnvironment(): void
    {
        // Try to load from $_ENV first, then getenv()
        $envMapping = [
            'pathao_user' => 'PATHAO_USER',
            'pathao_password' => 'PATHAO_PASSWORD',
            'steadfast_user' => 'STEADFAST_USER',
            'steadfast_password' => 'STEADFAST_PASSWORD',
            'redx_user' => 'REDX_USER',
            'redx_password' => 'REDX_PASSWORD',
        ];

        foreach ($envMapping as $configKey => $envKey) {
            $value = $this->getEnvironmentVariable($envKey);
            if ($value !== null) {
                $this->config[$configKey] = $value;
            }
        }
    }

    /**
     * Get environment variable from multiple sources
     * 
     * @param string $key Environment variable key
     * @return string|null Environment variable value
     */
    protected function getEnvironmentVariable(string $key): ?string
    {
        // Check $_ENV
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        // Check $_SERVER
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }

        // Check getenv()
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }

        // If Laravel env() function exists, try that too
        if (function_exists('env')) {
            $value = env($key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Validate required configuration
     * 
     * @param array $requiredKeys Required configuration keys
     * @throws \InvalidArgumentException If required configuration is missing
     */
    public function validateRequired(array $requiredKeys): void
    {
        $missing = [];
        
        foreach ($requiredKeys as $key) {
            if (empty($this->get($key))) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                'Missing required configuration: ' . implode(', ', $missing)
            );
        }
    }
} 