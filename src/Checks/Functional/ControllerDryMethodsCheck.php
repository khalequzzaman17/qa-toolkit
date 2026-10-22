<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class ControllerDryMethodsCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'No controller method is 50+ lines (extract to action)'; }

    public function run()
    {
        $path = app_path('Http/Controllers');
        if (! is_dir($path)) {
            return $this->pass('No controllers directory.');
        }

        $fs = new Filesystem();
        $chatty = [];

        foreach ($fs->allFiles($path) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';

            // Tokenize to find public function bodies.
            $tokens = token_get_all($contents);
            $count  = count($tokens);
            $inMethod = false;
            $depth   = 0;
            $startLine = 0;
            $method = '';

            for ($i = 0; $i < $count; $i++) {
                $token = $tokens[$i];

                if (is_array($token) && $token[0] === T_FUNCTION) {
                    for ($j = $i + 1; $j < $count; $j++) {
                        if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                            $method = $tokens[$j][1];
                            break;
                        }
                    }
                }

                if (is_array($token) && $token[0] === T_PUBLIC) {
                    // Look ahead for function.
                    $look = implode('', array_map(
                        fn($t) => is_array($t) ? $t[1] : $t,
                        array_slice($tokens, $i, 5)
                    ));
                    if (str_contains($look, 'function')) {
                        $inMethod = true;
                    }
                }

                if ($inMethod && is_string($token) && $token === '{') {
                    $depth++;
                    if ($depth === 1) {
                        $startLine = $tokens[$i-1] && is_array($tokens[$i-1]) ? $tokens[$i-1][2] : 0;
                    }
                }
                if ($inMethod && is_string($token) && $token === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $endLine = $tokens[$i-1] && is_array($tokens[$i-1]) ? $tokens[$i-1][2] : 0;
                        if ($endLine - $startLine > 50) {
                            $chatty[] = $file->getRelativePathname() . "::{$method} (line {$startLine}-{$endLine})";
                        }
                        $inMethod = false;
                        $method = '';
                    }
                }
            }
        }

        if (count($chatty) > 0) {
            return $this->warning(
                'Controller methods exceed 50 lines: ' . implode(', ', array_slice($chatty, 0, 5)),
                ['methods' => $chatty]
            );
        }

        return $this->pass('All controller methods are reasonably sized.');
    }
}
