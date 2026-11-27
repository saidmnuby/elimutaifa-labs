<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SelcomGateway
{
    public function configured(): bool
    {
        return filled(config('services.selcom.base_url'))
            && filled(config('services.selcom.api_key'))
            && filled(config('services.selcom.api_secret'))
            && filled(config('services.selcom.vendor_id'));
    }

    public function createOrder(array $payload): array
    {
        $response = $this->request('POST', '/v1/checkout/create-order', $payload);
        $json = $this->json($response);

        if (($json['resultcode'] ?? null) !== '000' || ($json['result'] ?? null) !== 'SUCCESS') {
            throw new RuntimeException('Selcom could not create the checkout order.');
        }

        $encodedUrl = data_get($json, 'data.0.payment_gateway_url');
        $url = is_string($encodedUrl) ? base64_decode($encodedUrl, true) : false;
        if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            $url = $encodedUrl;
        }
        if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            throw new RuntimeException('Selcom returned an invalid secure checkout link.');
        }

        return ['response' => $json, 'url' => $url];
    }

    public function orderStatus(string $orderId): array
    {
        $response = $this->request('GET', '/v1/checkout/order-status', ['order_id' => $orderId]);

        return $this->json($response);
    }

    public function validWebhook(array $payload, array $headers): bool
    {
        $key = (string) config('services.selcom.api_key');
        $secret = (string) config('services.selcom.api_secret');
        $timestamp = (string) ($headers['timestamp'] ?? '');
        $signedFields = (string) ($headers['signed-fields'] ?? '');
        $authorization = (string) ($headers['authorization'] ?? '');
        $method = (string) ($headers['digest-method'] ?? '');
        $digest = (string) ($headers['digest'] ?? '');

        try {
            $timestampSeconds = CarbonImmutable::parse($timestamp)->getTimestamp();
        } catch (\Throwable) {
            return false;
        }

        if (! $this->configured() || $method !== 'HS256' || $digest === '' || $signedFields === ''
            || $timestamp === '' || $authorization !== 'SELCOM '.base64_encode($key)
            || abs(time() - $timestampSeconds) > 300) {
            return false;
        }

        $fields = explode(',', $signedFields);
        if (count($fields) !== count(array_unique($fields))) {
            return false;
        }

        $parts = ['timestamp='.$timestamp];
        foreach ($fields as $field) {
            if ($field === '' || ! array_key_exists($field, $payload) || is_array($payload[$field])) {
                return false;
            }
            $parts[] = $field.'='.strval($payload[$field]);
        }

        $expected = base64_encode(hash_hmac('sha256', implode('&', $parts), $secret, true));

        return hash_equals($expected, $digest);
    }

    private function request(string $method, string $path, array $payload): Response
    {
        if (! $this->configured()) {
            throw new RuntimeException('Selcom payment settings are not configured.');
        }

        $baseUrl = rtrim((string) config('services.selcom.base_url'), '/');
        if (parse_url($baseUrl, PHP_URL_SCHEME) !== 'https') {
            throw new RuntimeException('Selcom API must use HTTPS.');
        }

        $timestamp = now()->toIso8601String();
        $signedFields = array_keys($payload);
        $signingParts = ['timestamp='.$timestamp];
        foreach ($payload as $field => $value) {
            $signingParts[] = $field.'='.strval($value);
        }

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'SELCOM '.base64_encode((string) config('services.selcom.api_key')),
            'Digest-Method' => 'HS256',
            'Digest' => base64_encode(hash_hmac(
                'sha256',
                implode('&', $signingParts),
                (string) config('services.selcom.api_secret'),
                true,
            )),
            'Timestamp' => $timestamp,
            'Signed-Fields' => implode(',', $signedFields),
        ];

        $request = Http::withHeaders($headers)->timeout(25)->connectTimeout(10);
        $response = $method === 'GET'
            ? $request->get($baseUrl.$path, $payload)
            : $request->post($baseUrl.$path, $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Selcom payment service is temporarily unavailable.');
        }

        return $response;
    }

    private function json(Response $response): array
    {
        $json = $response->json();
        if (! is_array($json)) {
            throw new RuntimeException('Selcom returned an unreadable response.');
        }

        return $json;
    }
}
