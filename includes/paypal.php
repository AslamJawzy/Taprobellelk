<?php
/** Minimal PayPal REST (Orders v2) client using cURL - no Composer needed. */

function paypal_configured(): bool { return PAYPAL_CLIENT_ID !== '' && PAYPAL_SECRET !== ''; }

function paypal_http(string $method, string $path, ?array $json = null, ?string $bearer = null, bool $tokenRequest = false): array {
    $ch = curl_init(PAYPAL_API_BASE . $path);
    $headers = ['Accept: application/json'];
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => CURL_VERIFY_SSL,
        CURLOPT_SSL_VERIFYHOST => CURL_VERIFY_SSL ? 2 : 0,
    ];
    if ($tokenRequest) {
        $opts[CURLOPT_USERPWD]    = PAYPAL_CLIENT_ID . ':' . PAYPAL_SECRET;
        $opts[CURLOPT_POSTFIELDS] = 'grant_type=client_credentials';
    } else {
        $headers[] = 'Authorization: Bearer ' . $bearer;
        $headers[] = 'Content-Type: application/json';
        $opts[CURLOPT_POSTFIELDS] = json_encode($json ?? new stdClass());
    }
    $opts[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    if ($body === false) { $err = curl_error($ch); curl_close($ch); throw new RuntimeException('Payment service unreachable: ' . $err); }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'data' => json_decode($body, true) ?: [], 'raw' => $body];
}

function paypal_token(): string {
    $r = paypal_http('POST', '/v1/oauth2/token', null, null, true);
    if ($r['code'] !== 200 || empty($r['data']['access_token'])) throw new RuntimeException('Could not authenticate with PayPal (check your Client ID / Secret).');
    return $r['data']['access_token'];
}

function paypal_create_order(array $order): string {
    $r = paypal_http('POST', '/v2/checkout/orders', [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'reference_id' => $order['order_no'],
            'description'  => 'Taprobellelk order ' . $order['order_no'],
            'amount'       => ['currency_code' => $order['currency'], 'value' => amount_str((float)$order['total'])],
        ]],
    ], paypal_token());
    if ($r['code'] >= 300 || empty($r['data']['id'])) throw new RuntimeException('PayPal could not create the order.');
    return $r['data']['id'];
}

/** @return array PayPal capture response */
function paypal_capture_order(string $paypalOrderId): array {
    return paypal_http('POST', '/v2/checkout/orders/' . rawurlencode($paypalOrderId) . '/capture', null, paypal_token());
}
