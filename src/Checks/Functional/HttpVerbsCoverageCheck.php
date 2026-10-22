<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Route;

class HttpVerbsCoverageCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Resource routes use all 7 verbs (index/create/store/show/edit/update/destroy)'; }

    public function run()
    {
        $resourceless = [];
        foreach (Route::getRoutes() as $route) {
            $uses = $route->getAction('uses');
            if (! is_string($uses) || ! str_contains($uses, '@')) continue;

            [$class, $method] = explode('@', $uses, 2);
            if (! in_array($method, ['index', 'store', 'show', 'edit', 'update', 'destroy', 'create'], true)) {
                continue;
            }

            $resource = $class;

            if (! isset($resourceless[$resource])) {
                $resourceless[$resource] = [];
            }
            $resourceless[$resource][] = $method;
        }

        $incomplete = [];
        $expected   = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
        foreach ($resourceless as $class => $methods) {
            $methods = array_unique($methods);
            sort($methods);
            if (count($methods) === 7) continue;
            // not full — could be API resource (5 verbs), allow that too
            if (count(array_intersect(['index', 'store', 'show', 'update', 'destroy'], $methods)) === 5) continue;
            $incomplete[] = $class . ' (' . implode(',', $methods) . ')';
        }

        if ($incomplete) {
            return $this->warning(
                'Partial resource controllers detected: ' . implode(', ', array_slice($incomplete, 0, 5)),
                ['incomplete' => $incomplete]
            );
        }

        return $this->pass('Resource controllers expose all expected verbs.');
    }
}
