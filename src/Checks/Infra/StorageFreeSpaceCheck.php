<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class StorageFreeSpaceCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Storage volume has > 10% free disk space'; }

    public function run()
    {
        $path = storage_path();
        $free = @disk_free_space($path);
        $total = @disk_total_space($path);

        if ($free === false || $total === false || $total <= 0) {
            return $this->warning('Could not inspect disk space.');
        }

        $pctFree = ($free / $total) * 100;

        if ($pctFree < 5) {
            return $this->critical(
                sprintf('Free disk space: %.2f%% (below 5%% threshold).', $pctFree),
                ['free_pct' => $pctFree, 'free_gb' => $free / 1024 / 1024 / 1024]
            );
        }

        if ($pctFree < 10) {
            return $this->warning(
                sprintf('Free disk space: %.2f%% (below 10%% threshold).', $pctFree),
                ['free_pct' => $pctFree]
            );
        }

        return $this->pass(sprintf('Free disk space: %.2f%%.', $pctFree), ['free_pct' => $pctFree]);
    }
}
