<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class CookieSettingsCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'Session cookies are HttpOnly, Secure, SameSite'; }

    public function run()
    {
        $httpOnly = config('session.http_only');
        $secure   = config('session.secure');
        $sameSite = (string) config('session.same_site', 'lax');
        $env      = (string) config('app.env');

        $issues = [];

        if ($httpOnly !== true) {
            $issues[] = 'session.http_only is not true';
        }

        if ($env === 'production' && $secure !== true) {
            $issues[] = 'session.secure is not true (cookies sent over HTTP)';
        }

        if (! in_array(strtolower($sameSite), ['lax', 'strict'], true)) {
            $issues[] = "session.same_site = '{$sameSite}' (recommend 'lax' or 'strict')";
        }

        if ($issues) {
            return $this->error(
                'Insecure cookie settings: ' . implode(' | ', $issues),
                ['issues' => $issues]
            );
        }

        return $this->pass('Session cookies are HttpOnly, Secure, SameSite.');
    }
}
