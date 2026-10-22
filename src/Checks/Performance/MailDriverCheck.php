<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;

class MailDriverCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'MAIL_MAILER is not log/smtp in production'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $mailer = (string) config('mail.default', config('mail.driver', ''));

        if (in_array($mailer, ['log', 'array', 'smtp'], true)) {
            return $this->warning(
                "MAIL_MAILER = '{$mailer}' in production — use ses/mailgun/postmark/resend.",
                ['mailer' => $mailer]
            );
        }

        return $this->pass("MAIL_MAILER = '{$mailer}'.");
    }
}
