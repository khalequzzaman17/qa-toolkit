<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class SslCertValidityCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'SSL cert for APP_URL is valid for > 30 days'; }

    public function run()
    {
        $url = (string) config('app.url');
        if (! str_starts_with($url, 'https://')) {
            return $this->pass("APP_URL not https — skipping cert check.");
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return $this->warning("Could not parse host from APP_URL '{$url}'.");
        }

        $context = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false]]);
        $socket  = @stream_socket_client("ssl://{$host}:443", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);

        if (! $socket) {
            return $this->warning("Could not connect to {$host}:443 — {$errstr}");
        }

        $params = stream_context_get_params($socket);
        fclose($socket);

        $cert = $params['options']['ssl']['peer_certificate'] ?? null;
        if (! $cert) {
            return $this->warning('No certificate returned by peer.');
        }

        $validTo = (int) openssl_x509_parse($cert)['validTo_time_t'] ?? 0;
        $days = $validTo > 0 ? (int) ceil(($validTo - time()) / 86400) : 0;

        if ($days < 0) {
            return $this->critical("SSL cert for {$host} is EXPIRED.");
        }
        if ($days < 7) {
            return $this->critical("SSL cert for {$host} expires in {$days} days.");
        }
        if ($days < 30) {
            return $this->warning("SSL cert for {$host} expires in {$days} days.");
        }

        return $this->pass("SSL cert for {$host} valid for {$days} more days.");
    }
}
