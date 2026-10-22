<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class NPlusOneInViewCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'No model->relation loops in Blade (foreach + ->relation)'; }

    public function run()
    {
        $fs = new Filesystem();
        $views = resource_path('views');

        if (! is_dir($views)) {
            return $this->pass('No views directory.');
        }

        $offenders = [];
        foreach ($fs->allFiles($views) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';

            // @foreach ($users as $user) ... {{ $user->profile->name }} — N+1
            if (preg_match('/@foreach\s*\([^)]*\bas\s+\$(\w+)/', $contents, $m)) {
                $var = preg_quote($m[1], '/');
                if (preg_match('/\$' . $var . '->(\w+)->(\w+)/', $contents)) {
                    $offenders[] = $file->getRelativePathname();
                }
            }
        }

        if ($offenders) {
            return $this->warning(
                'Possible N+1 in Blade templates: ' . implode(', ', array_unique($offenders)),
                ['files' => array_values(array_unique($offenders))]
            );
        }

        return $this->pass('No Blade templates look like N+1 loops.');
    }
}
