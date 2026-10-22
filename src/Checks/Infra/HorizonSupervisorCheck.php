<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class HorizonSupervisorCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Horizon has supervisors configured with sensible sizes'; }

    public function run()
    {
        $horizon = config('horizon');
        if (! is_array($horizon)) {
            return $this->pass('Horizon not installed.');
        }

        $supervisors = $horizon['supervisors'] ?? [];
        if (empty($supervisors)) {
            return $this->warning('Horizon has no supervisors configured.');
        }

        foreach ($supervisors as $name => $config) {
            $minProcesses = $config['minProcesses'] ?? null;
            $maxProcesses = $config['maxProcesses'] ?? null;
            if ($minProcesses === null || $maxProcesses === null) {
                return $this->warning("Horizon supervisor '{$name}' missing minProcesses/maxProcesses.");
            }
            if ($minProcesses < 2) {
                return $this->warning("Horizon supervisor '{$name}' minProcesses = {$minProcesses} (too low).");
            }
            if ($maxProcesses < $minProcesses) {
                return $this->error("Horizon supervisor '{$name}' maxProcesses < minProcesses.");
            }
        }

        return $this->pass('Horizon supervisors are well-configured.', ['count' => count($supervisors)]);
    }
}
