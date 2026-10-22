<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class TimezoneCorrectCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'app.timezone matches the business timezone'; }

    public function run()
    {
        $tz = (string) config('app.timezone', 'UTC');
        $env = (string) config('app.env');

        if ($env === 'production' && $tz === 'UTC') {
            return $this->warning(
                "APP_TIMEZONE = UTC — verify this matches the business timezone to avoid schedule drift.",
                ['timezone' => $tz]
            );
        }

        try {
            new \DateTimeZone($tz);
        } catch (\Throwable $e) {
            return $this->error("Invalid app.timezone = '{$tz}'.");
        }

        return $this->pass("app.timezone = '{$tz}'.");
    }
}
