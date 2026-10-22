<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class ErrorViewsCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Custom error views for 401/403/404/419/500'; }

    public function run()
    {
        $dir = resource_path('views/errors');
        if (! is_dir($dir)) {
            return $this->warning('No resources/views/errors directory — users see the default Laravel error page.');
        }

        $fs = new Filesystem();
        $required = ['401.blade.php', '403.blade.php', '404.blade.php', '419.blade.php', '500.blade.php'];
        $missing = [];

        foreach ($required as $file) {
            if (! file_exists($dir . '/' . $file)) {
                $missing[] = $file;
            }
        }

        if ($missing) {
            return $this->warning(
                'Missing error views: ' . implode(', ', $missing),
                ['missing' => $missing]
            );
        }

        return $this->pass('All standard error views exist.');
    }
}
