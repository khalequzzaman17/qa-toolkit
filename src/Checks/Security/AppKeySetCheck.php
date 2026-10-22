<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class AppKeySetCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'APP_KEY is set and 32+ chars (cipher key)'; }

    public function run()
    {
        $key = config('app.key');

        if (empty($key)) {
            return $this->critical('APP_KEY is missing. User sessions and encrypted data are unprotected.');
        }

        if (str_starts_with($key, 'base64:')) {
            $raw = base64_decode(substr($key, 7), true) ?: '';
        } else {
            $raw = $key;
        }

        if (strlen($raw) < 32) {
            return $this->error('APP_KEY is shorter than 32 bytes; rotate with `php artisan key:generate --force`.');
        }

        return $this->pass('APP_KEY set and length is sufficient.');
    }
}
