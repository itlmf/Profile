<?php

class IntegrationService
{
    public function __construct(private array $config)
    {
    }

    public function pullFromExternalSqlApi(string $endpoint): array
    {
        $base = rtrim($this->config['integrations']['external_sql_api_base'], '/');
        $token = $this->config['integrations']['external_sql_api_token'];

        if (!$base || !$token) {
            throw new RuntimeException('External SQL API is not configured.');
        }

        $url = $base . '/' . ltrim($endpoint, '/');
        return $this->requestJson($url, [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ]);
    }

    public function pullFromKoboSubmissions(): array
    {
        $base = rtrim($this->config['integrations']['kobo_base'], '/');
        $token = $this->config['integrations']['kobo_token'];
        $asset = $this->config['integrations']['kobo_asset_uid'];

        if (!$token || !$asset) {
            throw new RuntimeException('KoboToolbox integration is not configured.');
        }

        $url = $base . '/api/v2/assets/' . rawurlencode($asset) . '/data/';

        return $this->requestJson($url, [
            'Authorization: Token ' . $token,
            'Accept: application/json'
        ]);
    }

    private function requestJson(string $url, array $headers): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);
        $raw = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($raw ?: '', true);
        if ($status >= 400) {
            throw new RuntimeException('HTTP request failed: ' . $status);
        }

        return is_array($decoded) ? $decoded : [];
    }
}
