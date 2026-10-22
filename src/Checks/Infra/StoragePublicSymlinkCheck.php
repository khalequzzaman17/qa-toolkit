<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class StoragePublicSymlinkCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'public/storage symlink resolves correctly'; }

    public function run()
    {
        $link = public_path('storage');
        if (! is_link($link)) {
            return $this->warning('public/storage symlink is missing — run `php artisan storage:link`.');
        }

        $target = readlink($link);
        if (! is_dir($target)) {
            return $this->critical("public/storage symlink target '{$target}' is not a directory.");
        }

        return $this->pass('public/storage symlink is valid.', ['target' => $target]);
    }
}
