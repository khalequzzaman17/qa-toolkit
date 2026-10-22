<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;

class ModelStrictModeCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'Eloquent $shouldBeStrict enabled in production'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        // Newer Laravel (10.x+): Model::shouldBeStrict().
        $strict = \Illuminate\Database\Eloquent\Model::preventsAccessToMissingAttributes();
        $lazyDisabled = \Illuminate\Database\Eloquent\Model::preventsLazyLoading();

        if (! $strict) {
            return $this->warning(
                'Model::shouldBeStrict() is not enabled; missing-attribute access silently returns null.',
                ['strict' => false]
            );
        }

        if (! $lazyDisabled) {
            return $this->warning('Model::preventLazyLoading is off — N+1 queries slip into production.');
        }

        return $this->pass('Eloquent strict mode + preventLazyLoading are enabled.');
    }
}
