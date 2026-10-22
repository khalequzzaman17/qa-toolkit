<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationsUpToDateCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'All migrations are applied (no pending)'; }

    public function run()
    {
        if (! Schema::hasTable('migrations')) {
            return $this->warning('No migrations table — run `php artisan migrate`.');
        }

        $ran = DB::table('migrations')->pluck('migration')->toArray();
        $files = glob(base_path('database/migrations/*.php'));
        $files = array_map(fn($f) => basename($f, '.php'), $files);

        $pending = array_diff($files, $ran);

        if (count($pending) > 0) {
            return $this->critical(
                count($pending) . ' migrations are not applied. Run `php artisan migrate`.',
                ['pending' => array_values($pending)]
            );
        }

        $extra = array_diff($ran, $files);
        if (count($extra) > 0) {
            return $this->warning(
                count($extra) . ' migrations in DB are missing from filesystem.',
                ['extra' => array_values($extra)]
            );
        }

        return $this->pass('All migrations are applied.', ['total' => count($ran)]);
    }
}
