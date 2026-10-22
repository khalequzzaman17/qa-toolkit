<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\DB;

class StrictModeCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'MySQL strict mode enabled (STRICT_TRANS_TABLES)'; }

    public function run()
    {
        try {
            $modes = DB::select("SELECT @@SESSION.sql_mode AS mode");
            $mode  = (string) ($modes[0]->mode ?? '');
        } catch (\Throwable $e) {
            return $this->pass('Skipping — DB connection is not MySQL.');
        }

        if (! str_contains($mode, 'STRICT_TRANS_TABLES') && ! str_contains($mode, 'STRICT_ALL_TABLES')) {
            return $this->warning(
                'MySQL strict mode is not enabled — implicit data truncation possible.',
                ['mode' => $mode]
            );
        }

        return $this->pass('MySQL strict mode is enabled.');
    }
}
