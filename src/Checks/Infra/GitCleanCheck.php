<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class GitCleanCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Git working tree is clean at deploy time'; }

    public function run()
    {
        if (! file_exists(base_path('.git'))) {
            return $this->pass('No git repository; skipping.');
        }

        $status = (string) shell_exec('cd ' . escapeshellarg(base_path()) . ' && git status --porcelain 2>&1');

        if ($status === '' || $status === "\n") {
            return $this->pass('Git working tree is clean.');
        }

        $dirty = explode("\n", trim($status));
        $dirty = array_slice($dirty, 0, 5);

        return $this->warning(
            count($dirty) . ' uncommitted files (samples: ' . implode(', ', $dirty) . ')',
            ['dirty' => $dirty]
        );
    }
}
