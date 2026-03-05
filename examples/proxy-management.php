<?php

/**
 * MobileProxy.Space SDK — Proxy Management
 *
 * Examples of managing proxy settings, equipment, and blacklists.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use MobileProxy\Client;
use MobileProxy\Exception\ApiException;

$client = new Client('YOUR_API_TOKEN');

try {
    // ── Get available countries ──
    $countries = $client->getCountries('1'); // only available
    foreach ($countries as $country) {
        echo "{$country['id']}: {$country['name']}\n";
    }

    // ── Get prices for a country ──
    $prices = $client->getPrices(1); // country ID = 1
    print_r($prices);

    // ── Buy a proxy ──
    $order = $client->buyProxy([
        'id_country' => 1,
        'period'     => 30,         // 30 days
        'num'        => 1,
        'operator'   => 'MTS',
    ]);
    $proxyId = $order['proxy_id'];
    echo "Purchased proxy #{$proxyId}\n";

    // ── Change credentials ──
    $client->changeProxyCredentials($proxyId, 'mylogin', 'mypassword123');
    echo "Credentials updated.\n";

    // ── Edit settings: auto-reboot every 30 min, add IP auth ──
    $client->editProxy($proxyId, 30, '203.0.113.10', 'My scraping proxy');
    echo "Settings updated.\n";

    // ── Change equipment to a different operator/city ──
    $client->changeEquipment($proxyId, [
        'operator'          => 'Beeline',
        'add_to_black_list' => 1,    // blacklist current equipment
        'check_after_change'=> 1,    // verify IP after switch
    ]);
    echo "Equipment changed.\n";

    // ── Blacklist management ──
    $blacklist = $client->getBlackList($proxyId);
    print_r($blacklist);

    $client->addOperatorToBlackList($proxyId, 42); // block operator ID 42
    echo "Operator added to blacklist.\n";

    // ── Reboot the modem ──
    $client->rebootProxy($proxyId);
    echo "Proxy rebooted.\n";

} catch (ApiException $e) {
    echo "Error: {$e->getMessage()}\n";
    print_r($e->getResponseBody());
}
