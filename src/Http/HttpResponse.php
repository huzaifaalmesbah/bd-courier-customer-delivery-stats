<?php

namespace Ham\BdCourier\CustomerDeliveryStats\Http;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Cookie\CookieJar;

/**
 * HTTP Response Wrapper
 * 
 * Provides a consistent interface for HTTP responses.
 */
class HttpResponse
{
    protected $response;
    protected $cookies;

    /**
     * Constructor
     * 
     * @param ResponseInterface $response Guzzle response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $this->extractCookies();
    }

    /**
     * Get response body as string
     * 
     * @return string Response body
     */
    public function body(): string
    {
        return (string) $this->response->getBody();
    }

    /**
     * Get response as JSON array
     * 
     * @return array Decoded JSON response
     * @throws \InvalidArgumentException If response is not valid JSON
     */
    public function json(): array
    {
        $body = $this->body();
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Response is not valid JSON: ' . json_last_error_msg());
        }
        
        return $data ?? [];
    }

    /**
     * Get HTTP status code
     * 
     * @return int Status code
     */
    public function status(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Check if response was successful (2xx status code)
     * 
     * @return bool True if successful
     */
    public function successful(): bool
    {
        $status = $this->status();
        return $status >= 200 && $status < 300;
    }

    /**
     * Check if response was a redirect (3xx status code)
     * 
     * @return bool True if redirect
     */
    public function redirect(): bool
    {
        $status = $this->status();
        return $status >= 300 && $status < 400;
    }

    /**
     * Get response headers
     * 
     * @return array Response headers
     */
    public function headers(): array
    {
        return $this->response->getHeaders();
    }

    /**
     * Get specific header
     * 
     * @param string $name Header name
     * @return string|null Header value
     */
    public function header(string $name): ?string
    {
        $headers = $this->response->getHeader($name);
        return !empty($headers) ? $headers[0] : null;
    }

    /**
     * Get cookies from response
     * 
     * @return array Cookies
     */
    public function cookies(): array
    {
        return $this->cookies ?? [];
    }

    /**
     * Get response as collection-like array (for Laravel compatibility)
     * 
     * @return array Response data
     */
    public function collect(): array
    {
        $contentType = $this->header('Content-Type') ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            return $this->json();
        }
        
        // For non-JSON responses, try to extract meaningful data
        $body = $this->body();
        
        // If it looks like JSON, try to decode it
        if (preg_match('/^\s*[\[\{]/', $body)) {
            try {
                return $this->json();
            } catch (\InvalidArgumentException $e) {
                // Not JSON, return as array with body
                return ['body' => $body];
            }
        }
        
        return ['body' => $body];
    }

    /**
     * Convert response to array
     * 
     * @return array Response as array
     */
    public function toArray(): array
    {
        return $this->collect();
    }

    /**
     * Extract cookies from response headers
     */
    protected function extractCookies(): void
    {
        $this->cookies = [];
        $setCookieHeaders = $this->response->getHeader('Set-Cookie');
        
        foreach ($setCookieHeaders as $cookieHeader) {
            $parts = explode(';', $cookieHeader);
            $nameValue = explode('=', trim($parts[0]), 2);
            
            if (count($nameValue) === 2) {
                $this->cookies[$nameValue[0]] = $nameValue[1];
            }
        }
    }

    /**
     * Get the original Guzzle response
     * 
     * @return ResponseInterface Original response
     */
    public function getOriginalResponse(): ResponseInterface
    {
        return $this->response;
    }
} 