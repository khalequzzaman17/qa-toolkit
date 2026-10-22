<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class PhpVersionCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'PHP runtime version meets the framework requirement'; }

    public function run()
    {
        $version = PHP_VERSION;
        $major   = (int) explode('.', $version)[0];

        if ($major < 8) {
            return $this->critical("PHP version {$version} is unsupported by Laravel 10/11.");
        }

        if ($major === 8 && (int) explode('.', $version)[1] < 1) {
            return $this->warning("PHP version {$version} — Laravel 10+ requires 8.1+.");
        }

        return $this->pass("PHP version is {$version}.");
    }
}
