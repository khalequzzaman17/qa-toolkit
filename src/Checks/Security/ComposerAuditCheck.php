<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class ComposerAuditCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'composer audit reports no advisories'; }

    public function run()
    {
        // Run `composer audit` and capture exit code + output.
        // Assumes composer binary is available in PATH.
        $binary = trim((string) shell_exec('command -v composer'));

        if ($binary === '') {
            return $this->warning('composer binary not available in PATH — skipping.');
        }

        $cwd = getcwd();
        chdir(base_path());

        $output = [];
        $code   = 0;

        exec(escapeshellarg($binary) . ' audit --no-ansi --no-interaction 2>&1', $output, $code);

        if ($cwd) chdir($cwd);

        $text = implode("\n", $output);

        if ($code !== 0) {
            return $this->critical(
                'composer audit reported advisories — review and update dependencies.',
                ['output' => $text]
            );
        }

        return $this->pass('composer audit reports no known advisories.');
    }
}
