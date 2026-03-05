# MobileProxy.Space PHP SDK

[![CI](https://github.com/mobileproxy/php-sdk/actions/workflows/ci.yml/badge.svg)](https://github.com/mobileproxy/php-sdk/actions/workflows/ci.yml)

Official PHP SDK for the [MobileProxy.Space](https://mobileproxy.space) API — private mobile proxies on real GSM devices across 52 countries.

## Features

- **Full API coverage** — all endpoints wrapped in typed, documented methods
- **PSR-4 autoloading** — install via Composer
- **Typed exceptions** — `ApiException`, `AuthenticationException`, `RateLimitException`
- **IP rotation** — dedicated `changeIp()` method with no rate limit
- **PHP 7.4+** compatible

## Installation

```bash
composer require mobirox/mobileproxy-sdk
```

## Quick Start

```php
use MobileProxy\Client;
use MobileProxy\Exception\ApiException;

$client = new Client('YOUR_API_TOKEN');

// Check balance
$balance = $client->getBalance();
echo "Balance: {$balance['balance']}";

// List active proxies
$proxies = $client->getMyProxy();

// Get current IP of a proxy
$ip = $client->getProxyIp(12345);

// Rotate IP (no rate limit)
$client->changeIp('your_proxy_key');
```

## API Methods

### Proxy Information

| Method | Description |
|--------|-------------|
| `getProxyIp($proxyId, $checkSpam)` | Get current IP address of a proxy |
| `getMyProxy($proxyId)` | List active proxies (all or specific) |
| `getIpStats()` | IP address statistics by GEO |

### Proxy Management

| Method | Description |
|--------|-------------|
| `changeProxyCredentials($proxyId, $login, $password)` | Change proxy login/password |
| `rebootProxy($proxyId)` | Restart the modem |
| `editProxy($proxyId, $rebootTime, $ipAuth, $comment)` | Update proxy settings |
| `changeIp($proxyKey, $format, $userAgent)` | Rotate IP (no rate limit) |

### Equipment & GEO

| Method | Description |
|--------|-------------|
| `changeEquipment($proxyId, $options)` | Switch modem/SIM/operator/city |
| `getAvailableEquipment($proxyId, $options)` | List available equipment by GEO |
| `getGeoList($proxyId, $geoId)` | Available GEOs for a proxy |
| `getOperators($geoId)` | Operators for a GEO |
| `getCountries($onlyAvailable)` | List of countries |
| `getCities()` | List of cities |

### Blacklist

| Method | Description |
|--------|-------------|
| `getBlackList($proxyId)` | Get equipment/operator blacklist |
| `addOperatorToBlackList($proxyId, $operatorId)` | Block an operator |
| `removeOperatorFromBlackList($proxyId, $operatorId)` | Unblock an operator |
| `removeFromBlackList($proxyId, $blackListId, $eid)` | Remove equipment from blacklist |

### Purchasing & Billing

| Method | Description |
|--------|-------------|
| `buyProxy($options)` | Purchase a proxy |
| `refundProxy($proxyId)` | Request a refund |
| `getBalance()` | Account balance |
| `getPrices($countryId)` | Prices for a country |
| `getTestProxy($geoId, $operator)` | Get a free 2-hour trial proxy |

### Utilities

| Method | Description |
|--------|-------------|
| `checkEquipmentAvailability($eid)` | Check if equipment is available |
| `viewUrlFromDifferentIps($url, $countryId)` | Anti-cloaking: view URL from another country |
| `getTaskResult($taskId)` | Get async task result |

## Error Handling

```php
use MobileProxy\Exception\ApiException;
use MobileProxy\Exception\AuthenticationException;
use MobileProxy\Exception\RateLimitException;

try {
    $client->getBalance();
} catch (AuthenticationException $e) {
    // Invalid API token
} catch (RateLimitException $e) {
    // Too many requests — back off and retry
    // Limit: 3 × (number of active proxies) requests/sec
} catch (ApiException $e) {
    echo $e->getMessage();
    echo $e->getHttpCode();
    print_r($e->getResponseBody());
}
```

## Configuration

```php
$client = new Client('YOUR_API_TOKEN', [
    'timeout' => 120,    // request timeout in seconds
    'debug'   => true,   // enable cURL verbose output
]);
```

## Rate Limits

API requests are limited to **3 × (number of active proxies)** per second. For example, 10 proxies = 30 req/s. The `changeIp()` method uses a separate endpoint with **no rate limit**.

## Requirements

- PHP 7.4 or higher
- cURL extension
- JSON extension

## Links

- [API Documentation](https://mobileproxy.space/en/user.html?api)
- [Dashboard](https://mobileproxy.space/en/user.html)
- [Website](https://mobileproxy.space)
- [Chrome Extension](https://chromewebstore.google.com/detail/mobile-proxy-manager/lhbdhjhflkejgkkhlgacbaogbaaollac)

## License

MIT — see [LICENSE](LICENSE).
