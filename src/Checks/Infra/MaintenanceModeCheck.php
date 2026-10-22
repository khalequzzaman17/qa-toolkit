<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class MaintenanceModeCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'App is NOT in maintenance mode (down command not active)'; }

    public function run()
    {
        $down = storage_path('framework/down');

        if (file_exists($down)) {
            return $this->critical(
                'App is in maintenance mode (`php artisan down` is active). Run `php artisan up` before launch.'
            );
        }

        return $this->pass('App is live (`up` mode).');
    }
}
