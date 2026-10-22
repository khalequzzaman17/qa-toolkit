<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;

class AuthRoutesPresentCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Login/logout/register/password reset routes exist'; }

    public function run()
    {
        $required = ['login', 'logout', 'register', 'password.request', 'password.email', 'password.update'];
        $missing = [];

        foreach ($required as $name) {
            try {
                route($name);
            } catch (\Throwable $e) {
                $missing[] = $name;
            }
        }

        if ($missing) {
            return $this->warning(
                'Auth routes missing: ' . implode(', ', $missing),
                ['missing' => $missing]
            );
        }

        return $this->pass('All required auth routes are registered.');
    }
}
