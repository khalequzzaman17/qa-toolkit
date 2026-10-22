<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class AppEnvCorrectCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'APP_ENV is "production" in prod deploys'; }

    public function run()
    {
        $env = (string) config('app.env');

        // Tag the build pipeline with a marker file if running under CI.
        $marker = base_path('.qa-production-deploy');

        if (file_exists($marker) && $env !== 'production') {
            return $this->critical(
                "Deploying to production but APP_ENV = '{$env}' — set APP_ENV=production in .env.",
                ['env' => $env]
            );
        }

        return $this->pass("APP_ENV = '{$env}'.");
    }
}
