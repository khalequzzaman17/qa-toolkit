<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class ReadmeExistsCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Project has README.md + .env.example'; }

    public function run()
    {
        $files = [
            base_path('README.md'),
            base_path('.env.example'),
            base_path('composer.json'),
        ];

        $missing = [];
        foreach ($files as $file) {
            if (! file_exists($file)) {
                $missing[] = basename($file);
            }
        }

        if ($missing) {
            return $this->warning('Missing onboarding files: ' . implode(', ', $missing));
        }

        return $this->pass('README.md and .env.example are present.');
    }
}
