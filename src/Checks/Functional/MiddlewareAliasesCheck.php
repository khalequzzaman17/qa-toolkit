<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class MiddlewareAliasesCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Custom middleware aliases referenced in routes exist'; }

    public function run()
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $reflection = new \ReflectionClass($kernel);
        $prop = $reflection->getProperty('routeMiddleware');
        $prop->setAccessible(true);
        $aliasMap = $prop->getValue($kernel);

        if (! is_array($aliasMap)) {
            // Laravel 11 — use bootstrap/app.php
            $aliasMap = [];
        }

        $fs = new Filesystem();
        $routeFiles = [base_path('routes/web.php'), base_path('routes/api.php')];

        $used = [];
        foreach ($routeFiles as $file) {
            if (! file_exists($file)) continue;
            $contents = file_get_contents($file) ?: '';
            preg_match_all('/middleware\(\s*[\'"]([^\'"|]+)[\'"]/', $contents, $m);
            foreach ($m[1] as $alias) {
                $used[] = $alias;
            }
            preg_match_all('/->middleware\(\s*\[\s*([^\]]+)\]/', $contents, $m2);
            foreach ($m2[1] as $group) {
                preg_match_all('/[\'"]([a-z_:]+)[\'"]/i', $group, $aliases);
                foreach ($aliases[1] as $a) $used[] = $a;
            }
        }

        $used = array_unique($used);
        $missing = [];

        foreach ($used as $alias) {
            $base = explode(':', $alias, 2)[0];
            if (in_array($base, ['auth', 'guest', 'verified', 'signed', 'throttle', 'can', 'password.confirm', 'cache.headers', 'bindings', 'substitutebindings', 'encryptCookies', 'shareErrorsFromSession'], true)) continue;
            if (! isset($aliasMap[$base]) && ! class_exists($base)) {
                $missing[] = $base;
            }
        }

        if ($missing) {
            return $this->error(
                'Middleware aliases referenced in routes are not registered: ' . implode(', ', $missing),
                ['missing' => $missing]
            );
        }

        return $this->pass('All middleware aliases referenced in routes exist.');
    }
}
