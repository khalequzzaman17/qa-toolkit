<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class SmtpCredentialsCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Mailer credentials are set (host/port/user/pass)'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $default = (string) config('mail.default');
        $driver  = (string) config("mail.mailers.{$default}.transport");
        $host    = config("mail.mailers.{$default}.host");
        $user    = config("mail.mailers.{$default}.username");
        $pass    = config("mail.mailers.{$default}.password");

        // Only check SMTP for completeness.
        if ($driver !== 'smtp') {
            return $this->pass("Mailer transport = '{$driver}' — SMTP credentials check skipped.");
        }

        $missing = [];
        if (empty($host)) $missing[] = 'host';
        if (empty($user)) $missing[] = 'username';
        if (empty($pass)) $missing[] = 'password';

        if ($missing) {
            return $this->critical(
                "SMTP mailer missing credentials: " . implode(', ', $missing),
                ['missing' => $missing]
            );
        }

        return $this->pass('SMTP credentials are present.');
    }
}
