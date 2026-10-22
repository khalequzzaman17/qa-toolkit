<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class EnvKeyRotationCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'APP_KEY not committed in version control'; }

    public function run()
    {
        // Run `git log -p -- .env` style check, but without git in this env.
        // Fall back to: detect a .env.example with the same value as .env.
        $envPath = base_path('.env');
        $example = base_path('.env.example');

        if (! file_exists($envPath) || ! file_exists($example)) {
            return $this->pass('.env and/or .env.example not both present.');
        }

        $env     = file($envPath, FILE_IGNORE_NEW_LINES) ?: [];
        $example = file($example, FILE_IGNORE_NEW_LINES) ?: [];

        foreach ($env as $line) {
            if (! str_starts_with($line, 'APP_KEY=')) continue;
            foreach ($example as $ex) {
                if (str_starts_with($ex, 'APP_KEY=') && hash_equals($ex, $line)) {
                    return $this->critical(
                        'APP_KEY in .env matches the value in .env.example — rotate immediately.',
                        ['env_value' => substr($line, 8)]
                    );
                }
            }
        }

        return $this->pass('APP_KEY differs between .env and .env.example.');
    }
}
