<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\DB;

class SchemaForeignKeysCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'Migration FK definitions are explicit (cascade rules)'; }

    public function run()
    {
        $dir = base_path('database/migrations');
        if (! is_dir($dir)) {
            return $this->pass('No migrations directory.');
        }

        $fs = new \Illuminate\Filesystem\Filesystem();
        $files = $fs->allFiles($dir);

        $implicit = [];
        foreach ($files as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';
            // Look for $table->foreignId('user_id') without ->constrained() / ->references()
            if (preg_match_all('/foreignId\(\s*[\'"][a-z_]+[\'"]\s*\)/', $contents, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $m) {
                    $snippet = substr($contents, $m[1], 200);
                    if (! preg_match('/->constrained\(/', $snippet) && ! preg_match('/->references\(/', $snippet)) {
                        $implicit[] = $file->getFilename() . ':' . $m[0];
                    }
                }
            }
        }

        if ($implicit) {
            return $this->warning(
                'Foreign-key columns without explicit constraints: ' . implode(', ', array_slice($implicit, 0, 5)),
                ['offenders' => $implicit]
            );
        }

        return $this->pass('All foreignId() columns declare constraints.');
    }
}
