<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class MailQueueCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Mailables implement ShouldQueue (no synchronous email)'; }

    public function run()
    {
        $dir = app_path('Mail');
        if (! is_dir($dir)) {
            return $this->pass('No app/Mail directory.');
        }

        $fs = new Filesystem();
        $sync = [];
        foreach ($fs->allFiles($dir) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';
            if (preg_match('/class\s+(\w+)/', $contents, $m)) {
                $class = 'App\\Mail\\' . $m[1];
                if (class_exists($class) && ! in_array(\Illuminate\Contracts\Queue\ShouldQueue::class, class_implements($class) ?: [], true)) {
                    $sync[] = $class;
                }
            }
        }

        if ($sync) {
            return $this->warning(
                'Mailables sent synchronously (HTTP-blocking): ' . implode(', ', $sync),
                ['sync' => $sync]
            );
        }

        return $this->pass('All Mailables are queued.');
    }
}
