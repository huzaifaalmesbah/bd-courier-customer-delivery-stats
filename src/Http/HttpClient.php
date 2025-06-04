<?php

namespace Ham\BdCourier\CustomerDeliveryStats\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Cookie\CookieJar;

/**
 * HTTP Client Wrapper
 * 
 * A universal HTTP client that works with any PHP framework.
 * Uses Guzzle HTTP client under the hood.
 */
class HttpClient
{
    protected $client;
    protected $cookies;
    protected $defaultOptions = [];

    /**
     * Constructor
     * 
     * @param array $options Default Guzzle options
     */
    public function __construct(array $options = [])
    {
        $this->cookies = new CookieJar();
        $this->defaultOptions = array_merge([
            'timeout' => 30,
            'verify' => true,
            'cookies' => $this->cookies,
        ], $options);
        
        $this->client = new Client($this->defaultOptions);
    }

    /**
     * Make a GET request
     * 
     * @param string $url URL to request
     * @param array $options Additional options
     * @return HttpResponse Response object
     */
    public function get(string $url, array $options = []): HttpResponse
    {
        try {
            $response = $this->client->get($url, $this->mergeOptions($options));
            return new HttpResponse($response);
        } catch (RequestException $e) {
            throw new \Exception('HTTP GET request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Make a POST request
     * 
     * @param string $url URL to request
     * @param array $data Data to send
     * @param array $options Additional options
     * @return HttpResponse Response object
     */
    public function post(string $url, array $data = [], array $options = []): HttpResponse
    {
        try {
            $options = $this->mergeOptions($options);
            
            // Determine content type
            if (isset($options['form_params']) || (isset($options['headers']['Content-Type']) && 
                strpos($options['headers']['Content-Type'], 'application/x-www-form-urlencoded') !== false)) {
                $options['form_params'] = array_merge($options['form_params'] ?? [], $data);
            } else {
                $options['json'] = $data;
            }
            
            $response = $this->client->post($url, $options);
            return new HttpResponse($response);
        } catch (RequestException $e) {
            throw new \Exception('HTTP POST request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Set headers for subsequent requests
     * 
     * @param array $headers Headers to set
     * @return self
     */
    public function withHeaders(array $headers): self
    {
        $this->defaultOptions['headers'] = array_merge(
            $this->defaultOptions['headers'] ?? [],
            $headers
        );
        return $this;
    }

    /**
     * Set cookies for subsequent requests
     * 
     * @param array $cookies Cookies to set
     * @param string $domain Domain for cookies
     * @return self
     */
    public function withCookies(array $cookies, string $domain = ''): self
    {
        foreach ($cookies as $name => $value) {
            $this->cookies->setCookie(new \GuzzleHttp\Cookie\SetCookie([
                'Name' => $name,
                'Value' => $value,
                'Domain' => $domain,
            ]));
        }
        return $this;
    }

    /**
     * Set form data content type
     * 
     * @return self
     */
    public function asForm(): self
    {
        return $this->withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]);
    }

    /**
     * Get cookies
     * 
     * @return CookieJar Cookie jar
     */
    public function getCookies(): CookieJar
    {
        return $this->cookies;
    }

    /**
     * Clear cookies
     * 
     * @return self
     */
    public function clearCookies(): self
    {
        $this->cookies->clear();
        return $this;
    }

    /**
     * Merge options with defaults
     * 
     * @param array $options Options to merge
     * @return array Merged options
     */
    protected function mergeOptions(array $options): array
    {
        return array_merge_recursive($this->defaultOptions, $options);
    }
} 