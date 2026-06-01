<?php

namespace App\Services;

use App\Traits\ExceptionLogger;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;

class BaseClientService {
    use ExceptionLogger;

    protected ?Client $client = null;
    protected array $config = [];
    protected array $defaultHeaders = [];
    protected bool $isDebugHttpClient = false;

    public function __construct(array $config = []) {
        $this->configure($config);
    }

    /**
     * Configure service dengan default values
     */
    protected function configure(array $config = []): void {
        $request = request();

        $this->config = array_merge([
            'base_url' => null,
            'token' => null,
            'x_api_key' => null,
            'timeout' => 5,
            'retries' => 3,
            'retry_delay' => 200,
            'service_name' => 'Unknown',
            'default_headers' => [],
            'http_errors' => false,
            'verify' => true, // SSL verification
        ], $config);

        $this->defaultHeaders = array_merge([
            'Accept' => 'application/json',
            'Accept-Language' => $request->header('Accept-Language'),
            'Content-Type' => 'application/json',
            'User-Agent' => 'Apps-Control-Center-Service-Client/1.0',
            'X-Profiling' => $request->header('X-Profiling'),
            'X-Request-ID' => $request->header('X-Request-ID'),
            'X-Log-ID' => $request->header('X-Log-ID'),
        ], $this->config['default_headers']);

        if ($this->hasConfig('base_url') && !$this->client)
            $this->initializeClient();
    }

    /**
     * Initialize Guzzle client
     */
    protected function initializeClient(): void {
        $headers = $this->defaultHeaders;

        if ($this->hasConfig('token'))
            $headers['Authorization'] = 'Bearer ' . $this->getConfig('token');

        if ($this->hasConfig('x_api_key'))
            $headers['X-API-Key-Client'] = $this->getConfig('x_api_key');

        $this->client = new Client([
            'base_uri' => rtrim($this->getConfig('base_url'), '/') . '/',
            'timeout' => $this->getConfig('timeout'),
            'connect_timeout' => $this->getConfig('timeout'),
            'headers' => $headers,
            'http_errors' => $this->getConfig('http_errors'),
            'verify' => $this->getConfig('verify'),
            'debug' => $this->isDebugHttpClient
        ]);
    }

    /**
     * Make HTTP request dengan retry logic
     */
    protected function request(string $method, string $uri, array $options = []): mixed {
        $this->ensureClientInitialized();

        $attempts = 0;
        $maxRetries = $this->getConfig('retries');
        $serviceName = $this->getConfig('service_name');

        while ($attempts <= $maxRetries) {
            try {
                $response = $this->client->request($method, ltrim($uri, '/'), $options);
                $statusCode = $response->getStatusCode();
                $body = $response->getBody()->getContents();

                if ($statusCode >= 200 && $statusCode < 300)
                    return $this->parseResponse($body);

                //* Client errors (4xx) - no retry
                if ($statusCode >= 400 && $statusCode < 500) {
                    /*
                    $this->logClientError($method, $uri, $statusCode, $body, $serviceName);

                    throw new Exception("{$serviceName} Client Error: HTTP {$statusCode}", $statusCode);
                    */
                    return $this->parseResponse($body);
                }

                //* Server errors (5xx) - retry
                $attempts++;
                if ($attempts <= $maxRetries) {
                    $this->logRetry($method, $uri, $statusCode, $attempts, $serviceName);
                    usleep($this->getConfig('retry_delay') * 1000);

                    continue;
                }

                //* Max retries reached for server error
                throw new Exception("{$serviceName} Server Error: HTTP {$statusCode} after {$maxRetries} retries", $statusCode);
            } catch (ConnectException $e) {
                $attempts++;
                if ($attempts <= $maxRetries) {
                    $this->logConnectionError($method, $uri, $attempts, $e, $serviceName);
                    usleep($this->getConfig('retry_delay') * 1000);

                    continue;
                }

                //* Max retries reached for connection error
                throw new Exception("{$serviceName} Connection Failed: " . $e->getMessage() . " after {$maxRetries} attempts", $statusCode);
            } catch (Exception $e) {
                //* Hanya log exception yang belum dilog sebelumnya
                if (!str_contains($e->getMessage(), 'already logged'))
                    $this->logAndNotifyServiceError($e, $method, $uri, $serviceName);

                throw $e;
            }
        }

        return null;
    }

    /**
     * Helper methods untuk HTTP verbs
     */
    protected function get(string $uri, array $query = [], array $headers = []): mixed {
        return $this->request('GET', $uri, [
            'query' => $query, // query params
            'headers' => array_merge($this->defaultHeaders, $headers),
        ]);
    }

    protected function post(string $uri, array $data = [], array $query = [], array $headers = []): mixed {
        return $this->request('POST', $uri, [
            'json' => $data,
            'query'   => $query, // query params
            'headers' => array_merge($this->defaultHeaders, $headers),
        ]);
    }

    protected function put(string $uri, array $data = [], array $query = [], array $headers = []): mixed {
        return $this->request('PUT', $uri, [
            'json' => $data,
            'query'   => $query, // query params
            'headers' => array_merge($this->defaultHeaders, $headers),
        ]);
    }

    protected function delete(string $uri, array $data = [], array $query = [], array $headers = []): mixed {
        return $this->request('DELETE', $uri, [
            'json' => $data,
            'query'   => $query, // query params
            'headers' => array_merge($this->defaultHeaders, $headers),
        ]);
    }

    protected function patch(string $uri, array $data = [], array $query = [], array $headers = []): mixed {
        return $this->request('PATCH', $uri, [
            'json' => $data,
            'query'   => $query, // query params
            'headers' => array_merge($this->defaultHeaders, $headers),
        ]);
    }

    /**
     * Configuration management
     */
    protected function getConfig(string $key, mixed $default = null): mixed {
        return $this->config[$key] ?? $default;
    }

    protected function hasConfig(string $key): bool {
        return isset($this->config[$key]) && !empty($this->config[$key]);
    }

    public function setConfig(string $key, mixed $value): self {
        $this->config[$key] = $value;

        //* Reinitialize client jika base_url atau token berubah
        if (in_array($key, ['base_url', 'token']) && $this->client)
            $this->initializeClient();

        return $this;
    }

    public function setToken(string $token): self {
        return $this->setConfig('token', $token);
    }

    public function setServiceApiKey(string $apiKey): self {
        return $this->setConfig('X-API-Key', $apiKey);
    }

    public function setBaseUrl(string $baseUrl): self {
        return $this->setConfig('base_url', $baseUrl);
    }

    /**
     * Response handling
     */
    protected function parseResponse(string $body): mixed {
        $decoded = json_decode($body, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $body;
    }

    /**
     * Validation & initialization
     */
    protected function ensureClientInitialized(): void {
        if (!$this->client)
            throw new Exception("Service client not initialized. Call configure() first.");
    }

    /**
     * Centralized logging and notification for service errors
     */
    private function logAndNotifyServiceError(Exception $e, string $method, string $uri, string $serviceName): void {
        $statusCode = $this->getStatusCodeFromException($e);

        //* Add service context to exception message
        $enhancedMessage = "[{$serviceName}] {$method} {$uri} - {$e->getMessage()}";

        //* Create new exception with enhanced message
        $enhancedException = new Exception($enhancedMessage, $e->getCode(), $e);

        //* Log using the trait (includes Discord notification)
        $this->logAndNotify($enhancedException, $statusCode);
    }

    /**
     * Determine HTTP status code from exception type
     */
    private function getStatusCodeFromException(Exception $e): int {
        $codeError = $e->getCode() !== 0 ? $e->getCode() : null;

        if (str_contains($e->getMessage(), 'Client Error')) {
            return $codeError ?? 400;
        } elseif (str_contains($e->getMessage(), 'Server Error')) {
            return $codeError ?? 500;
        } elseif (str_contains($e->getMessage(), 'Connection Failed')) {
            return $codeError ?? 503;
        }

        return 500;
    }

    /**
     * Logging methods - hanya log internal, tidak panggil logAndNotify
     */
    protected function logClientError(string $method, string $uri, int $statusCode, string $body, string $serviceName): void {
        $response = $this->parseResponse($body);

        $context = [
            'service' => $serviceName,
            'method' => $method,
            'uri' => $uri,
            'status' => $statusCode,
            'response' => $response,
        ];

        //* Hanya log internal, logAndNotify akan dipanggil di logAndNotifyServiceError
        Log::warning("Client error in {$serviceName}", $context);
    }

    protected function logRetry(string $method, string $uri, int $statusCode, int $attempt, string $serviceName): void {
        $context = [
            'service' => $serviceName,
            'method' => $method,
            'uri' => $uri,
            'status' => $statusCode,
            'attempt' => $attempt,
            'next_attempt_in_ms' => $this->getConfig('retry_delay'),
        ];

        Log::warning("Retrying {$serviceName} request", $context);
    }

    protected function logConnectionError(string $method, string $uri, int $attempt, ConnectException $e, string $serviceName): void {
        $context = [
            'service' => $serviceName,
            'method' => $method,
            'uri' => $uri,
            'attempt' => $attempt,
            'error' => $e->getMessage(),
        ];

        Log::error("Connection error in {$serviceName}", $context);
    }
}
