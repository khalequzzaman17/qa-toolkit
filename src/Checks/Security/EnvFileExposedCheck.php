<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use QaToolkit\Support\Cfg;

class EnvFileExposedCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return '.env is not web-accessible & not in version control'; }

    public function run()
    {
        $issues = [];

        // 1. Public docroot contains a readable .env file.
        $public = base_path('public/.env');
        if (file_exists($public)) {
            $issues[] = 'A .env file exists in ' . base_path('public') . '/ (web-accessible).';
        }

        // 2. .gitignore does not list .env.
        $gitignore = base_path('.gitignore');
        if (file_exists($gitignore)) {
            $content = file_get_contents($gitignore) ?: '';
            if (! preg_match('/^\.env$/m', $content) && ! preg_match('/^\.env\s/m', $content)) {
                $issues[] = 'No `.env` entry in .gitignore; risk of committing secrets.';
            }
        }

        // 3. .env file contains keys flagged as known-leaked.
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $env = file_get_contents($envPath) ?: '';
            foreach (['AWS_SECRET_ACCESS_KEY', 'STRIPE_SECRET', 'GITHUB_TOKEN', 'SLACK_TOKEN'] as $secret) {
                if (preg_match('/^\s*' . $secret . '\s*=\s*\S+/m', $env)) {
                    $issues[] = "{$secret} is hardcoded in .env (consider moving to secrets manager).";
                }
            }
        }

        if ($issues) {
            return $this->critical(implode(' | ', $issues), ['issues' => $issues]);
        }

        return $this->pass('.env is correctly protected.');
    }
}
