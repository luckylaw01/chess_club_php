<?php

function paystack_get_setting_value(mysqli $conn, string $settingKey): string
{
    $sql = "SELECT setting_value FROM app_settings WHERE setting_key = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return '';
    }

    $stmt->bind_param('s', $settingKey);
    $stmt->execute();
    $result = $stmt->get_result();
    $value = '';

    if ($result && ($row = $result->fetch_assoc())) {
        $value = (string)($row['setting_value'] ?? '');
    }

    $stmt->close();
    return $value;
}

function paystack_get_keys(mysqli $conn): array
{
    return [
        'secret_key' => paystack_get_setting_value($conn, 'paystack_secret_key'),
        'public_key' => paystack_get_setting_value($conn, 'paystack_public_key'),
    ];
}

function paystack_api_request(string $method, string $url, string $secretKey, ?array $payload = null): array
{
    $ch = curl_init($url);

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/json',
            'Cache-Control: no-cache',
        ],
        CURLOPT_TIMEOUT => 45,
    ];

    if ($payload !== null) {
        $options[CURLOPT_POSTFIELDS] = json_encode($payload);
    }

    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return [
            'status' => false,
            'message' => $error ?: 'Unable to contact Paystack.',
            'status_code' => $statusCode,
        ];
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        return [
            'status' => false,
            'message' => 'Invalid response from Paystack.',
            'raw' => $response,
            'status_code' => $statusCode,
        ];
    }

    $decoded['status_code'] = $statusCode;
    return $decoded;
}

function paystack_base_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

function paystack_page_url(string $page, array $query = []): string
{
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    if ($basePath === '' || $basePath === '.') {
        $basePath = '';
    }

    $url = paystack_base_url() . $basePath . '/' . ltrim($page, '/');
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    return $url;
}

function paystack_generate_reference(string $prefix = 'PSK'): string
{
    return strtoupper($prefix) . '-' . strtoupper(bin2hex(random_bytes(6)));
}
