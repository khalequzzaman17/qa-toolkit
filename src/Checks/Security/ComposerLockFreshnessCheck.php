<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class ComposerLockFreshnessCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'composer.lock exists and matches composer.json'; }

    public function run()
    {
        $lock = base_path('composer.lock');
        $json = base_path('composer.json');

        if (! file_exists($lock)) {
            return $this->critical('composer.lock is missing — run `composer install`.');
        }
        if (! file_exists($json)) {
            return $this->pass('composer.json missing — skipping.');
        }

        $lockTime = filemtime($lock);
        $jsonTime = filemtime($json);

        if ($jsonTime > $lockTime) {
            return $this->warning('composer.json is newer than composer.lock — run `composer update --lock`.');
        }

        return $this->pass('composer.lock is fresh.');
    }
}
