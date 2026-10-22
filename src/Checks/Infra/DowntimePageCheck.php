<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class DowntimePageCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Maintenance mode renders a 503 view, not the default page'; }

    public function run()
    {
        $view = resource_path('views/errors/503.blade.php');
        if (file_exists($view)) {
            return $this->pass('Custom 503 maintenance view exists.');
        }

        return $this->warning('No 503.blade.php — `php artisan down` will show the default Laravel maintenance page.');
    }
}
