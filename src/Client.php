<?php

declare(strict_types=1);

namespace MobileProxy;

use MobileProxy\Exception\ApiException;

/**
 * MobileProxy.Space API Client
 *
 * Official PHP SDK for interacting with the MobileProxy.Space API.
 * Provides private mobile proxies on real GSM devices across 52 countries.
 *
 * @see https://mobileproxy.space/api.html
 * @license MIT
 */
class Client
{
    private HttpClient $http;

    /**
     * Create a new MobileProxy.Space API client.
     *
     * @param string $apiToken Your API token (find it in your dashboard)
     * @param array  $options  Optional settings:
     *                         - 'base_url' (string) API endpoint override
     *                         - 'timeout'  (int)    Request timeout in seconds (default: 60)
     *                         - 'debug'    (bool)   Enable cURL verbose output (default: false)
     */
    public function __construct(string $apiToken, array $options = [])
    {
        $this->http = new HttpClient($apiToken, $options);
    }

    // ──────────────────────────────────────────────
    //  Proxy Information
    // ──────────────────────────────────────────────

    /**
     * Get the current IP address of a proxy.
     *
     * @param int         $proxyId   Proxy ID
     * @param string|null $checkSpam Also check IP against spam databases (pass "1" to enable)
     * @return array{ip: string, country: string, ...}
     * @throws ApiException
     */
    public function getProxyIp(int $proxyId, ?string $checkSpam = null): array
    {
        $params = ['proxy_id' => $proxyId];
        if ($checkSpam !== null) {
            $params['check_spam'] = $checkSpam;
        }
        return $this->http->request('proxy_ip', 'GET', $params);
    }

    /**
     * Get a list of your active proxies.
     *
     * @param int|null $proxyId Filter by specific proxy ID, or null for all proxies
     * @return array
     * @throws ApiException
     */
    public function getMyProxy(?int $proxyId = null): array
    {
        $params = [];
        if ($proxyId !== null) {
            $params['proxy_id'] = $proxyId;
        }
        return $this->http->request('get_my_proxy', 'GET', $params);
    }

    /**
     * Get IP address statistics by GEO.
     *
     * @return array
     * @throws ApiException
     */
    public function getIpStats(): array
    {
        return $this->http->request('get_ipstat', 'GET');
    }

    // ──────────────────────────────────────────────
    //  Proxy Management
    // ──────────────────────────────────────────────

    /**
     * Change the login and password of a proxy.
     *
     * @param int    $proxyId  Proxy ID
     * @param string $login    New login
     * @param string $password New password
     * @return array
     * @throws ApiException
     */
    public function changeProxyCredentials(int $proxyId, string $login, string $password): array
    {
        return $this->http->request('change_proxy_login_password', 'GET', [
            'proxy_id'    => $proxyId,
            'proxy_login' => $login,
            'proxy_pass'  => $password,
        ]);
    }

    /**
     * Restart a proxy (reboot the modem).
     *
     * @param int $proxyId Proxy ID
     * @return array
     * @throws ApiException
     */
    public function rebootProxy(int $proxyId): array
    {
        return $this->http->request('reboot_proxy', 'GET', [
            'proxy_id' => $proxyId,
        ]);
    }

    /**
     * Change existing proxy settings.
     *
     * @param int         $proxyId       Proxy ID
     * @param int|null    $rebootTime    Auto-reboot interval in minutes (0 = disabled)
     * @param string|null $ipAuth        Authorized IP address (empty string to disable)
     * @param string|null $comment       Comment / label for this proxy
     * @return array
     * @throws ApiException
     */
    public function editProxy(int $proxyId, ?int $rebootTime = null, ?string $ipAuth = null, ?string $comment = null): array
    {
        $params = ['proxy_id' => $proxyId];
        if ($rebootTime !== null) {
            $params['proxy_reboot_time'] = $rebootTime;
        }
        if ($ipAuth !== null) {
            $params['proxy_ipauth'] = $ipAuth;
        }
        if ($comment !== null) {
            $params['proxy_comment'] = $comment;
        }
        return $this->http->request('edit_proxy', 'GET', $params);
    }

    // ──────────────────────────────────────────────
    //  Equipment & GEO
    // ──────────────────────────────────────────────

    /**
     * Change proxy equipment (modem/SIM).
     *
     * @param int         $proxyId          Proxy ID
     * @param array       $options          Optional parameters:
     *                                      - 'operator'          (string) Target operator name
     *                                      - 'geoid'             (int)    GEO ID
     *                                      - 'id_country'        (int)    Country ID
     *                                      - 'id_city'           (int)    City ID
     *                                      - 'eid'               (int)    Equipment ID
     *                                      - 'add_to_black_list' (mixed)  Add current to blacklist
     *                                      - 'check_after_change'(mixed)  Check IP after change
     *                                      - 'check_spam'        (mixed)  Check spam after change
     * @return array
     * @throws ApiException
     */
    public function changeEquipment(int $proxyId, array $options = []): array
    {
        $params = ['proxy_id' => $proxyId];
        $allowed = ['operator', 'geoid', 'id_country', 'id_city', 'eid', 'add_to_black_list', 'check_after_change', 'check_spam'];
        foreach ($allowed as $key) {
            if (isset($options[$key])) {
                $params[$key] = $options[$key];
            }
        }
        return $this->http->request('change_equipment', 'GET', $params);
    }

    /**
     * Get available equipment grouped by GEO and operator.
     *
     * @param int   $proxyId  Proxy ID
     * @param array $options  Optional parameters:
     *                        - 'equipments_back_list' (int)   Exclude equipment IDs
     *                        - 'operators_back_list'  (int)   Exclude operator IDs
     *                        - 'show_count_null'      (mixed) Show items with 0 count
     * @return array
     * @throws ApiException
     */
    public function getAvailableEquipment(int $proxyId, array $options = []): array
    {
        $params = ['proxy_id' => $proxyId];
        $allowed = ['equipments_back_list', 'operators_back_list', 'show_count_null'];
        foreach ($allowed as $key) {
            if (isset($options[$key])) {
                $params[$key] = $options[$key];
            }
        }
        return $this->http->request('get_geo_operator_list', 'GET', $params);
    }

    /**
     * Get list of available GEOs for a proxy.
     *
     * @param int $proxyId Proxy ID
     * @param int $geoId   GEO ID
     * @return array
     * @throws ApiException
     */
    public function getGeoList(int $proxyId, int $geoId): array
    {
        return $this->http->request('get_geo_list', 'GET', [
            'proxy_id' => $proxyId,
            'geoid'    => $geoId,
        ]);
    }

    /**
     * Get list of operators for a GEO.
     *
     * @param int $geoId GEO ID
     * @return array
     * @throws ApiException
     */
    public function getOperators(int $geoId): array
    {
        return $this->http->request('get_operators_list', 'GET', [
            'geoid' => $geoId,
        ]);
    }

    /**
     * Get list of countries.
     *
     * @param string|null $onlyAvailable Filter to only available countries
     * @return array
     * @throws ApiException
     */
    public function getCountries(?string $onlyAvailable = null): array
    {
        $params = [];
        if ($onlyAvailable !== null) {
            $params['only_avaliable'] = $onlyAvailable;
        }
        return $this->http->request('get_id_country', 'GET', $params);
    }

    /**
     * Get list of cities.
     *
     * @return array
     * @throws ApiException
     */
    public function getCities(): array
    {
        return $this->http->request('get_id_city', 'GET');
    }

    // ──────────────────────────────────────────────
    //  Blacklist Management
    // ──────────────────────────────────────────────

    /**
     * Get the blacklist of equipment and operators for a proxy.
     *
     * @param int $proxyId Proxy ID
     * @return array
     * @throws ApiException
     */
    public function getBlackList(int $proxyId): array
    {
        return $this->http->request('get_black_list', 'GET', [
            'proxy_id' => $proxyId,
        ]);
    }

    /**
     * Add an operator to the blacklist.
     *
     * @param int $proxyId    Proxy ID
     * @param int $operatorId Operator ID
     * @return array
     * @throws ApiException
     */
    public function addOperatorToBlackList(int $proxyId, int $operatorId): array
    {
        return $this->http->request('add_operator_to_black_list', 'GET', [
            'proxy_id'    => $proxyId,
            'operator_id' => $operatorId,
        ]);
    }

    /**
     * Remove an operator from the blacklist.
     *
     * @param int $proxyId    Proxy ID
     * @param int $operatorId Operator ID
     * @return array
     * @throws ApiException
     */
    public function removeOperatorFromBlackList(int $proxyId, int $operatorId): array
    {
        return $this->http->request('remove_operator_black_list', 'GET', [
            'proxy_id'    => $proxyId,
            'operator_id' => $operatorId,
        ]);
    }

    /**
     * Remove an entry from the equipment blacklist.
     *
     * @param int $proxyId     Proxy ID
     * @param int $blackListId Blacklist entry ID
     * @param int $eid         Equipment ID
     * @return array
     * @throws ApiException
     */
    public function removeFromBlackList(int $proxyId, int $blackListId, int $eid): array
    {
        return $this->http->request('remove_black_list', 'GET', [
            'proxy_id'      => $proxyId,
            'black_list_id' => $blackListId,
            'eid'           => $eid,
        ]);
    }

    // ──────────────────────────────────────────────
    //  Purchasing & Billing
    // ──────────────────────────────────────────────

    /**
     * Purchase a proxy.
     *
     * @param array $options Purchase parameters:
     *                       - 'id_country'   (int)    Country ID (required)
     *                       - 'period'       (int)    Rental period in days (required)
     *                       - 'num'          (int)    Number of proxies (default: 1)
     *                       - 'operator'     (string) Preferred operator
     *                       - 'geoid'        (int)    GEO ID
     *                       - 'id_city'      (int)    City ID
     *                       - 'coupons_code' (string) Discount coupon code
     *                       - 'auto_renewal' (mixed)  Enable auto-renewal
     *                       - 'proxy_id'     (int)    Extend an existing proxy
     * @return array
     * @throws ApiException
     */
    public function buyProxy(array $options): array
    {
        return $this->http->request('buyproxy', 'GET', $options);
    }

    /**
     * Refund a proxy.
     *
     * @param int $proxyId Proxy ID
     * @return array
     * @throws ApiException
     */
    public function refundProxy(int $proxyId): array
    {
        return $this->http->request('refund_proxy', 'GET', [
            'proxy_id' => $proxyId,
        ]);
    }

    /**
     * Get your account balance.
     *
     * @return array{balance: float, currency: string}
     * @throws ApiException
     */
    public function getBalance(): array
    {
        return $this->http->request('get_balance', 'GET');
    }

    /**
     * Get proxy prices for a country.
     *
     * @param int $countryId Country ID
     * @return array
     * @throws ApiException
     */
    public function getPrices(int $countryId): array
    {
        return $this->http->request('get_price', 'GET', [
            'id_country' => $countryId,
        ]);
    }

    /**
     * Get a free 2-hour test proxy.
     *
     * @param int    $geoId    GEO ID
     * @param string $operator Operator name
     * @return array
     * @throws ApiException
     */
    public function getTestProxy(int $geoId, string $operator): array
    {
        return $this->http->request('get_test_proxy', 'GET', [
            'geoid'    => $geoId,
            'operator' => $operator,
        ]);
    }

    // ──────────────────────────────────────────────
    //  Utilities
    // ──────────────────────────────────────────────

    /**
     * Check if a specific equipment is available.
     *
     * @param int $eid Equipment ID
     * @return array
     * @throws ApiException
     */
    public function checkEquipmentAvailability(int $eid): array
    {
        return $this->http->request('eid_avaliable', 'GET', [
            'eid' => $eid,
        ]);
    }

    /**
     * View a URL from different IPs (anti-cloaking tool).
     *
     * @param string $url       URL to fetch
     * @param int    $countryId Country ID to fetch from
     * @return array
     * @throws ApiException
     */
    public function viewUrlFromDifferentIps(string $url, int $countryId): array
    {
        return $this->http->request('see_the_url_from_different_IPs', 'POST', [
            'url'        => $url,
            'id_country' => $countryId,
        ]);
    }

    /**
     * Get the result of an async task.
     *
     * @param int $taskId Task ID
     * @return array
     * @throws ApiException
     */
    public function getTaskResult(int $taskId): array
    {
        return $this->http->request('tasks', 'GET', [
            'tasks_id' => $taskId,
        ]);
    }

    // ──────────────────────────────────────────────
    //  IP Rotation (via changeip.mobileproxy.space)
    // ──────────────────────────────────────────────

    /**
     * Change proxy IP address using the dedicated rotation endpoint.
     * This method uses a separate endpoint with NO rate limit (unlike other API methods).
     *
     * @param string $proxyKey  Proxy key from your dashboard (the key in the "Change IP" link)
     * @param string $format    Response format: 'json' or '0' for plain text
     * @param string $userAgent Custom User-Agent header (must not look like a bot)
     * @return array|string
     */
    public function changeIp(string $proxyKey, string $format = 'json', string $userAgent = 'MobileProxy-PHP-SDK/1.0'): array
    {
        $url = 'https://changeip.mobileproxy.space/?' . http_build_query([
            'proxy_key' => $proxyKey,
            'format'    => $format,
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => $userAgent,
        ]);

        $output = curl_exec($curl);

        if (curl_errno($curl) > 0) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new ApiException("IP change failed: {$error}");
        }

        curl_close($curl);

        if ($format === 'json') {
            $json = json_decode($output, true);
            return $json ?? ['response' => $output];
        }

        return ['response' => $output];
    }

    // ──────────────────────────────────────────────
    //  Debug
    // ──────────────────────────────────────────────

    /**
     * Get the last HTTP request details (for debugging).
     */
    public function getLastRequest(): array
    {
        return $this->http->getLastRequest();
    }

    /**
     * Get the last HTTP response details (for debugging).
     */
    public function getLastResponse(): array
    {
        return $this->http->getLastResponse();
    }
}
