<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;
use QaToolkit\Support\Cfg;

class RequiredEnvKeysCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Required env keys (APP_ENV, DB, CACHE, QUEUE, MAIL...) are set'; }

    public function run()
    {
        $required = Cfg::infra('required_env_keys');
        $missing = [];

        foreach ($required as $key) {
            $value = env($key);
            if ($value === null || $value === '') {
                $missing[] = $key;
            }
        }

        if ($missing) {
            return $this->critical(
                'Missing required env keys: ' . implode(', ', $missing),
                ['missing' => $missing]
            );
        }

        return $this->pass('All required env keys are set.', ['checked' => $required]);
    }
}
