<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class BcryptCostCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'Hasher bcrypt cost >= 10'; }

    public function run()
    {
        $hasher = app('hash');

        if (! method_exists($hasher, 'getRounds')) {
            return $this->pass('Hasher does not expose rounds (custom hasher).');
        }

        $rounds = $hasher->getRounds();

        if ($rounds < 10) {
            return $this->error(
                "Bcrypt cost factor is {$rounds}; raise to >= 10 (config('hashing.bcrypt.rounds')).",
                ['rounds' => $rounds]
            );
        }

        if ($rounds < 12) {
            return $this->warning("Bcrypt cost factor is {$rounds}; consider raising to 12 for production.");
        }

        return $this->pass("Bcrypt cost factor is {$rounds}.");
    }
}
