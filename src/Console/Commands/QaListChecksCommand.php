<?php

namespace QaToolkit\Console\Commands;

use Illuminate\Console\Command;

class QaListChecksCommand extends Command
{
    protected $signature = 'qa:checks';
    protected $description = 'List every check registered with the toolkit and its suite.';

    public function handle(): int
    {
        $all = app('qa-toolkit.checks');

        $rows = [];
        foreach ($all as $class => $check) {
            $rows[] = [$check->suite(), $check->id(), $check->label(), $class];
        }

        $this->table(['Suite', 'ID', 'Label', 'Class'], $rows);
        $this->info(count($rows) . ' checks registered.');
        return 0;
    }
}
