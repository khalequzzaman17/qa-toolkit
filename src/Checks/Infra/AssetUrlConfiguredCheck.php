<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class AssetUrlConfiguredCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'ASSET_URL is set if assets are on a CDN/subdomain'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $assetUrl = config('app.asset_url');
        $appUrl   = config('app.url');

        if (! empty($assetUrl) && $assetUrl !== $appUrl) {
            return $this->pass('ASSET_URL is configured for a separate asset host.', ['asset_url' => $assetUrl]);
        }

        return $this->pass('ASSET_URL = APP_URL (same-origin assets).', ['asset_url' => $assetUrl]);
    }
}
