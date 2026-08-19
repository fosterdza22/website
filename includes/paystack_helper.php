<?php
require_once __DIR__ . '/../config/paystack.php';

/**
 * Call Paystack's Initialize Transaction API.
 * Returns the decoded JSON response array, or ['status' => false, 'message' => '...'] on failure.
 */
function paystack_initialize($email, $amountGHS, $reference, $callbackUrl, $metadata = []) {
    $url = 'https://api.paystack.co/transaction/initialize';
    $fields = [
        'email' => $email,
        'amount' => (int)round($amountGHS * 100), // Paystack expects the amount in the lowest currency unit (pesewas)
        'currency' => PAYSTACK_CURRENCY,
        'reference' => $reference,
        'callback_url' => $callbackUrl,
        'metadata' => $metadata,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($fields),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['status' => false, 'message' => 'Connection to Paystack failed: ' . $error];
    }
    $decoded = json_decode($response, true);
    return $decoded ?: ['status' => false, 'message' => 'Invalid response from Paystack.'];
}

/**
 * Call Paystack's Verify Transaction API. Always trust this server-side
 * response over anything the browser reports.
 */
function paystack_verify($reference) {
    $url = 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . PAYSTACK_SECRET_KEY],
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['status' => false, 'message' => 'Connection to Paystack failed: ' . $error];
    }
    $decoded = json_decode($response, true);
    return $decoded ?: ['status' => false, 'message' => 'Invalid response from Paystack.'];
}

/** Generate a unique payment reference */
function paystack_generate_reference($prefix = 'HA') {
    return strtoupper($prefix) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
}
