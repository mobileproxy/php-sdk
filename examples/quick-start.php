<?php

/**
 * MobileProxy.Space SDK — Quick Start
 *
 * Get your API token from: https://mobileproxy.space/en/user.html
 */

require_once __DIR__ . '/../vendor/autoload.php';

use MobileProxy\Client;
use MobileProxy\Exception\ApiException;
use MobileProxy\Exception\RateLimitException;

$client = new Client('YOUR_API_TOKEN');

try {
    // Check account balance
    $balance = $client->getBalance();
    echo "Balance: {$balance['balance']}\n";

    // List your active proxies
    $proxies = $client->getMyProxy();
    echo "Active proxies: " . count($proxies) . "\n";

    // Get the current IP of a specific proxy
    $ip = $client->getProxyIp(12345);
    echo "Current IP: {$ip['ip']}\n";

    // Change IP (no rate limit on this endpoint)
    $result = $client->changeIp('your_proxy_key_here');
    echo "New IP: {$result['new_ip']}\n";

} catch (RateLimitException $e) {
    echo "Rate limited — wait and retry.\n";
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()} (HTTP {$e->getHttpCode()})\n";
}
