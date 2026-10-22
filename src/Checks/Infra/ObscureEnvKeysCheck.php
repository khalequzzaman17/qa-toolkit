<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class ObscureEnvKeysCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'No empty/duplicate env keys in .env'; }

    public function run()
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return $this->pass('No .env file — skipping.');
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES) ?: [];
        $seen = [];
        $empty = [];
        $duplicates = [];

        foreach ($lines as $line) {
            if (! str_contains($line, '=')) continue;
            if (str_starts_with(trim($line), '#')) continue;

            [$key, $value] = explode('=', $line, 2);

            if (trim($value) === '') {
                $empty[] = $key;
            }

            if (isset($seen[$key])) {
                $duplicates[] = $key;
            }
            $seen[$key] = true;
        }

        if ($empty || $duplicates) {
            $issues = [];
            if ($empty) $issues[] = 'empty: ' . implode(', ', $empty);
            if ($duplicates) $issues[] = 'duplicates: ' . implode(', ', $duplicates);
            return $this->warning('.env has ' . implode(' | ', $issues), ['empty' => $empty, 'duplicates' => $duplicates]);
        }

        return $this->pass('.env has no empty or duplicate keys.');
    }
}
