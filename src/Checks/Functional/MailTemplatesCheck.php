<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class MailTemplatesCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Mail templates use Mailable classes, not raw Mail::raw'; }

    public function run()
    {
        $fs = new Filesystem();
        $paths = [app_path(), base_path('routes')];
        $raw = [];

        foreach ($paths as $path) {
            if (! is_dir($path)) continue;
            foreach ($fs->allFiles($path) as $file) {
                if ($file->getExtension() !== 'php') continue;
                $contents = file_get_contents($file->getRealPath()) ?: '';
                if (preg_match('/Mail::raw\s*\(/', $contents)) {
                    $raw[] = $file->getRelativePathname();
                }
            }
        }

        if ($raw) {
            return $this->warning(
                'Raw Mail::raw() calls — convert to Mailable classes: ' . implode(', ', array_unique($raw)),
                ['files' => array_values(array_unique($raw))]
            );
        }

        return $this->pass('All outgoing mail uses Mailable classes.');
    }
}
