<?php

declare(strict_types=1);

namespace MobileProxy\Tests;

use PHPUnit\Framework\TestCase;
use MobileProxy\Client;
use MobileProxy\HttpClient;
use MobileProxy\Exception\ApiException;
use MobileProxy\Exception\AuthenticationException;
use MobileProxy\Exception\RateLimitException;

class ClientTest extends TestCase
{
    public function testClientCanBeInstantiated(): void
    {
        $client = new Client('test_token');
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testClientAcceptsOptions(): void
    {
        $client = new Client('test_token', [
            'timeout' => 120,
            'debug'   => false,
        ]);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testHttpClientCanBeInstantiated(): void
    {
        $http = new HttpClient('test_token');
        $this->assertInstanceOf(HttpClient::class, $http);
    }

    public function testHttpClientAcceptsCustomBaseUrl(): void
    {
        $http = new HttpClient('test_token', [
            'base_url' => 'https://custom.api.endpoint/api.html',
        ]);
        $this->assertInstanceOf(HttpClient::class, $http);
    }

    public function testApiExceptionCarriesDetails(): void
    {
        $body = ['error' => 'Something went wrong'];
        $exception = new ApiException('Test error', 400, $body);

        $this->assertEquals('Test error', $exception->getMessage());
        $this->assertEquals(400, $exception->getHttpCode());
        $this->assertEquals($body, $exception->getResponseBody());
    }

    public function testAuthenticationExceptionExtendsApiException(): void
    {
        $exception = new AuthenticationException('Invalid token', 401);
        $this->assertInstanceOf(ApiException::class, $exception);
        $this->assertEquals(401, $exception->getHttpCode());
    }

    public function testRateLimitExceptionExtendsApiException(): void
    {
        $exception = new RateLimitException('Too many requests', 429);
        $this->assertInstanceOf(ApiException::class, $exception);
        $this->assertEquals(429, $exception->getHttpCode());
    }

    public function testLastRequestIsEmptyInitially(): void
    {
        $client = new Client('test_token');
        $this->assertEmpty($client->getLastRequest());
    }

    public function testLastResponseIsEmptyInitially(): void
    {
        $client = new Client('test_token');
        $this->assertEmpty($client->getLastResponse());
    }
}
