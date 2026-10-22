<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class DefaultCredentialsCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'No default/weak DB credentials in production'; }

    public function run()
    {
        $env = (string) config('app.env');

        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $defaults = [
            'root'        => ['root', 'password', 'admin', 'secret', 'mysql', ''],
            'homestead'   => ['homestead', 'secret', 'password'],
            'forge'       => ['forge', 'secret', 'password'],
            'laravel'     => ['laravel', 'password', 'secret'],
        ];

        $user = (string) config('database.connections.mysql.username', '');
        $pass = (string) config('database.connections.mysql.password', '');

        $weakUser = in_array(strtolower($user), ['root', 'admin', 'sa', 'postgres', ''], true);

        if ($weakUser) {
            return $this->critical(
                "Production DB user is '{$user}' — use a least-privilege account.",
                ['username' => $user]
            );
        }

        foreach ($defaults as $defaultUser => $defaultPasses) {
            if (strtolower($user) === $defaultUser && in_array($pass, $defaultPasses, true)) {
                return $this->critical(
                    "Production DB credentials match the '{$defaultUser}' default — rotate immediately.",
                    ['username' => $user]
                );
            }
        }

        if (strlen($pass) < 12) {
            return $this->warning(
                'Production DB password is shorter than 12 characters.',
                ['length' => strlen($pass)]
            );
        }

        return $this->pass('Production DB credentials look non-default.');
    }
}
