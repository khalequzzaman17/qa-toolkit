<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class StorageLinkCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'storage:link symlink does not expose app/ or framework/'; }

    public function run()
    {
        $link = public_path('storage');

        if (! is_link($link)) {
            return $this->warning('No storage:link symlink. Run `php artisan storage:link`.', ['expected' => $link]);
        }

        $target = readlink($link);
        $base   = base_path();

        // If target is outside storage/app/public — flag.
        if (! str_starts_with($target, $base . '/storage/app/public')) {
            return $this->critical(
                "storage symlink target '{$target}' is outside storage/app/public — review for leakage.",
                ['target' => $target]
            );
        }

        return $this->pass('storage:link correctly points to storage/app/public.');
    }
}
