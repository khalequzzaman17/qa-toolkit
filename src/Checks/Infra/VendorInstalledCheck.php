<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class VendorInstalledCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'vendor/ directory exists and autoload is up-to-date'; }

    public function run()
    {
        if (! is_dir(base_path('vendor'))) {
            return $this->critical('vendor/ directory missing — run `composer install --no-dev --optimize-autoloader`.');
        }

        $autoload = base_path('vendor/composer/autoload_real.php');
        if (! file_exists($autoload)) {
            return $this->critical('Composer autoloader missing — run `composer dump-autoload --optimize`.');
        }

        return $this->pass('vendor/ and autoloader are present.');
    }
}
