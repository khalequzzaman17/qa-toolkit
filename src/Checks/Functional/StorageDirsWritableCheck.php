<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;

class StorageDirsWritableCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'storage/ + bootstrap/cache/ are writable'; }

    public function run()
    {
        $paths = [
            storage_path('app'),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        $unwritable = [];
        foreach ($paths as $p) {
            if (! file_exists($p)) {
                @mkdir($p, 0775, true);
            }
            if (! is_writable($p)) {
                $unwritable[] = $p;
            }
        }

        if ($unwritable) {
            return $this->critical(
                'Storage paths are not writable by the web user: ' . implode(', ', $unwritable),
                ['paths' => $unwritable]
            );
        }

        return $this->pass('All storage paths are writable.', ['checked' => $paths]);
    }
}
