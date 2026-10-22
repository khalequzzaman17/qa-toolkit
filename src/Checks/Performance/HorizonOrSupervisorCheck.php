<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;

class HorizonOrSupervisorCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'Queue supervisor (Horizon/Supervisor) configured for prod'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $horizonConfig = config('horizon');
        if (is_array($horizonConfig)) {
            $supervisors = $horizonConfig['supervisors'] ?? [];
            if (empty($supervisors)) {
                return $this->warning('Horizon is installed but has no supervisors configured.');
            }
            return $this->pass('Horizon is configured with supervisors.');
        }

        // Fall back to reading a supervisor/systemd config file.
        if (file_exists(base_path('supervisord.conf')) || file_exists(base_path('deploy/supervisord.conf'))) {
            return $this->pass('Supervisord configuration detected.');
        }

        return $this->warning(
            'Neither Horizon nor supervisord config detected — queue workers may not auto-restart.'
        );
    }
}
