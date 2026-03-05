<?php

/**
 * MobileProxy.Space SDK — Web Scraping Example
 *
 * Using mobile proxies for web scraping with automatic IP rotation.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use MobileProxy\Client;
use MobileProxy\Exception\ApiException;

$client = new Client('YOUR_API_TOKEN');

// Your proxy details (from dashboard)
$proxyHost  = 'proxy.mobileproxy.space';
$proxyPort  = 12345;  // your assigned port
$proxyUser  = 'your_login';
$proxyPass  = 'your_password';
$proxyKey   = 'your_proxy_key'; // for IP rotation

$urls = [
    'https://httpbin.org/ip',
    'https://httpbin.org/headers',
    'https://httpbin.org/user-agent',
];

foreach ($urls as $i => $url) {
    // Rotate IP before each request (no rate limit on this endpoint)
    if ($i > 0) {
        $client->changeIp($proxyKey);
        sleep(2); // give the modem a moment to reconnect
    }

    // Make request through the proxy
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_PROXY          => "{$proxyHost}:{$proxyPort}",
        CURLOPT_PROXYUSERPWD   => "{$proxyUser}:{$proxyPass}",
        CURLOPT_PROXYTYPE      => CURLPROXY_HTTP,
        CURLOPT_TIMEOUT        => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "[{$httpCode}] {$url}\n";
    echo $response . "\n\n";
}

echo "Done — scraped " . count($urls) . " URLs with IP rotation.\n";
