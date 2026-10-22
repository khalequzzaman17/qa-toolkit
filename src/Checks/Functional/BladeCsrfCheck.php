<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class BladeCsrfCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Every <form> in Blade includes @csrf'; }

    public function run()
    {
        $views = resource_path('views');
        if (! is_dir($views)) {
            return $this->pass('No resources/views directory.');
        }

        $fs = new Filesystem();
        $missing = [];

        foreach ($fs->allFiles($views) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';
            // Find <form ...> tags without @csrf in their scope.
            if (preg_match_all('/<form\b[^>]*>.*?<\/form>/is', $contents, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    [$formText, $offset] = $match;
                    if (! str_contains($formText, '@csrf') && ! str_contains($formText, 'csrf_field') && ! str_contains($formText, 'csrf_token')) {
                        $line = substr_count(substr($contents, 0, $offset), "\n") + 1;
                        $missing[] = $file->getRelativePathname() . ':' . $line;
                    }
                }
            }
        }

        if ($missing) {
            return $this->warning(
                'Forms missing @csrf directive: ' . implode(', ', array_slice($missing, 0, 5)),
                ['missing' => $missing]
            );
        }

        return $this->pass('All Blade forms include @csrf.');
    }
}
