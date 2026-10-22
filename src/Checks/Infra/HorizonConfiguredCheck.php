<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class HorizonConfiguredCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Horizon config published + dashboard gated in production'; }

    public function run()
    {
        $horizon = config('horizon');
        if (! is_array($horizon)) {
            return $this->pass('Horizon not installed; skipping.');
        }

        $gate = $horizon['gate'] ?? null;
        if (empty($gate) || $gate === 'Spatie\Horizon\Gate::check') {
            return $this->warning('Horizon gate is open — restrict to admins via config/horizon.php "gate" key.');
        }

        return $this->pass('Horizon gate is configured.', ['gate' => $gate]);
    }
}
