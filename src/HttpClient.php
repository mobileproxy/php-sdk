<?php

declare(strict_types=1);

namespace MobileProxy;

use MobileProxy\Exception\ApiException;
use MobileProxy\Exception\AuthenticationException;
use MobileProxy\Exception\RateLimitException;

class HttpClient
{
    private string $baseUrl;
    private string $apiToken;
    private int $timeout;
    private bool $debug;
    private array $lastRequest = [];
    private array $lastResponse = [];

    public function __construct(string $apiToken, array $options = [])
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('cURL extension is required. Install it with: apt install php-curl');
        }

        $this->apiToken = $apiToken;
        $this->baseUrl = $options['base_url'] ?? 'https://mobileproxy.space/api.html';
        $this->timeout = $options['timeout'] ?? 60;
        $this->debug = $options['debug'] ?? false;
    }

    /**
     * @throws ApiException
     * @throws AuthenticationException
     * @throws RateLimitException
     */
    public function request(string $command, string $method = 'GET', array $params = []): array
    {
        $params['command'] = $command;
        $query = http_build_query($params, '', '&');

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_VERBOSE        => $this->debug,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiToken,
                'Accept: application/json',
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT      => 'MobileProxy-PHP-SDK/1.0',
        ]);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_URL, $this->baseUrl . '?command=' . urlencode($command));
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        } else {
            curl_setopt($curl, CURLOPT_URL, $this->baseUrl . '?' . $query);
        }

        $this->lastRequest = [
            'command' => $command,
            'method'  => $method,
            'params'  => $params,
        ];

        $output = curl_exec($curl);

        if (curl_errno($curl) > 0) {
            $error = curl_error($curl);
            $errno = curl_errno($curl);
            curl_close($curl);
            throw new ApiException("cURL error ({$errno}): {$error}");
        }

        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($output, 0, $headerSize);
        $body = substr($output, $headerSize);

        curl_close($curl);

        $json = json_decode($body, true);

        $this->lastResponse = [
            'http_code' => $httpCode,
            'header'    => $header,
            'body'      => $body,
            'json'      => $json,
        ];

        if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException(
                'Invalid JSON response: ' . json_last_error_msg(),
                $httpCode
            );
        }

        // Handle API-level errors
        if (isset($json['error'])) {
            $message = $json['error'];

            if ($httpCode === 401 || stripos($message, 'auth') !== false || stripos($message, 'token') !== false) {
                throw new AuthenticationException($message, $httpCode, $json);
            }

            if ($httpCode === 429 || stripos($message, 'Too many requests') !== false) {
                throw new RateLimitException($message, $httpCode, $json);
            }

            throw new ApiException($message, $httpCode, $json);
        }

        return $json ?? [];
    }

    public function getLastRequest(): array
    {
        return $this->lastRequest;
    }

    public function getLastResponse(): array
    {
        return $this->lastResponse;
    }
}
