<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class UploadedAssetCdnCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'Public uploads go to S3/CDN, not local public disk'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $default = (string) config('filesystems.default');
        $public  = (string) config('filesystems.disks.public.driver');

        if ($default === 'local' || $public === 'local') {
            return $this->warning(
                'Public uploads land on the local disk — use s3/CDN for horizontal scale.',
                ['default' => $default, 'public' => $public]
            );
        }

        return $this->pass("Public disk driver = '{$public}'.");
    }
}
