<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class TlsEnabledCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'APP_URL uses https:// in production'; }

    public function run()
    {
        $env  = (string) config('app.env');
        $url  = (string) config('app.url');

        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        if (! str_starts_with($url, 'https://')) {
            return $this->critical(
                "APP_URL = '{$url}' — HTTPS is required in production.",
                ['url' => $url]
            );
        }

        return $this->pass('APP_URL uses HTTPS.');
    }
}
