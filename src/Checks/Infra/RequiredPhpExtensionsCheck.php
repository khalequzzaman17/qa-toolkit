<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class RequiredPhpExtensionsCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Required PHP extensions are loaded'; }

    public function run()
    {
        $required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'curl', 'fileinfo', 'dom'];
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            $required[] = 'iconv';
        }

        $missing = [];
        foreach ($required as $ext) {
            if (! extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        if ($missing) {
            return $this->critical(
                'Missing PHP extensions: ' . implode(', ', $missing),
                ['missing' => $missing]
            );
        }

        return $this->pass('All required PHP extensions are loaded.');
    }
}
