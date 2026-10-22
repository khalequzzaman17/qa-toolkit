<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class PasswordHashCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'No password strings hard-coded in app/ (only bcrypt)'; }

    public function run()
    {
        $fs = new Filesystem();
        $app = app_path();

        if (! is_dir($app)) {
            return $this->pass('No app/ directory.');
        }

        $offenders = [];
        $patterns = [
            // "password" => 'plaintext-string'   or  password = 'plaintext'
            '/[\'"]password[\'"]\s*=>\s*[\'"][^\'"]{6,}[\'"]/i',
            '/[\'"]password[\'"]\s*=\s*[\'"][^\'"]{6,}[\'"]/i',
            // ->password('plaintext') — dangerous in seeders for production users
            '/->password\(\s*[\'"][^\'"]{6,}[\'"]/',
        ];

        foreach ($fs->allFiles($app) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';
            foreach ($patterns as $p) {
                if (preg_match_all($p, $contents, $matches)) {
                    $offenders[] = $file->getRelativePathname() . ' (' . count($matches[0]) . ')';
                }
            }
        }

        if ($offenders) {
            return $this->warning(
                'Possible plaintext passwords in app/: ' . implode(', ', $offenders),
                ['files' => $offenders]
            );
        }

        return $this->pass('No plaintext passwords detected in app/.');
    }
}
