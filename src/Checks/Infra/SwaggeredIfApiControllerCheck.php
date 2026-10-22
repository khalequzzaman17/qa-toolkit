<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class SwaggeredIfApiControllerCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'API controllers apply API versioning if exposed'; }

    public function run()
    {
        // Heuristic — look for api/v1 in routes/api.php
        $api = base_path('routes/api.php');
        if (! file_exists($api)) {
            return $this->pass('No routes/api.php.');
        }

        $contents = file_get_contents($api) ?: '';
        if (preg_match('#/v\d+/#', $contents)) {
            return $this->pass('API versioning prefix detected.');
        }

        if (preg_match('#/api/#', $contents) === 0) {
            return $this->warning('API routes do not seem to use version prefixes (e.g. /v1).');
        }

        return $this->pass('API routes are unversioned but consistent.');
    }
}
