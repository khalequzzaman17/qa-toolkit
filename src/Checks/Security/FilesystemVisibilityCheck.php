<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Storage;

class FilesystemVisibilityCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'Public disk holds no sensitive files (.env, .sql, .zip)'; }

    public function run()
    {
        if (! app()->bound('filesystem')) {
            return $this->pass('Filesystem not bound; skipping.');
        }

        $public = Storage::disk('public');
        $sensitive = [];
        $patterns  = ['/.env$/i', '/\.sql$/i', '/\.zip$/i', '/\.bak$/i', '/\.tar$/i', '/\.log$/i'];

        try {
            $files = $public->allFiles();
        } catch (\Throwable $e) {
            return $this->warning('Could not enumerate public disk: ' . $e->getMessage());
        }

        foreach ($files as $file) {
            foreach ($patterns as $p) {
                if (preg_match($p, $file)) {
                    $sensitive[] = $file;
                    break;
                }
            }
        }

        if ($sensitive) {
            return $this->critical(
                'Public disk contains sensitive files: ' . implode(', ', array_slice($sensitive, 0, 10)),
                ['files' => $sensitive]
            );
        }

        return $this->pass('Public disk is clean of sensitive files.');
    }
}
