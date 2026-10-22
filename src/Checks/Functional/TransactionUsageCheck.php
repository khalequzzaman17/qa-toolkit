<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class TransactionUsageCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Multi-write flows wrapped in DB::transaction'; }

    public function run()
    {
        $fs = new Filesystem();
        $paths = [app_path('Http/Controllers'), app_path('Actions'), app_path('Services')];

        $offenders = [];
        foreach ($paths as $path) {
            if (! is_dir($path)) continue;
            foreach ($fs->allFiles($path) as $file) {
                $contents = file_get_contents($file->getRealPath()) ?: '';

                // Has more than one ->save() or ->create() / DB::table()->insert
                $writeCount = preg_match_all('/->save\(\)|::create\(|->create\(|::update\(|->update\(\)|->delete\(\)|DB::table\([^)]*\)->insert\(/', $contents);
                $hasTx      = preg_match('/DB::transaction\(|DB::beginTransaction\(/', $contents);

                if ($writeCount >= 2 && ! $hasTx) {
                    $offenders[] = $file->getRelativePathname() . " ({$writeCount} writes)";
                }
            }
        }

        if ($offenders) {
            return $this->warning(
                'Multi-write code without DB::transaction: ' . implode(', ', array_slice($offenders, 0, 5)),
                ['offenders' => $offenders]
            );
        }

        return $this->pass('Multi-write flows are wrapped in transactions.');
    }
}
