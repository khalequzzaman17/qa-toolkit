<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class HoneypotOrRecaptchaCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'Public forms use honeypot or reCAPTCHA for spam'; }

    public function run()
    {
        $composer = base_path('composer.json');
        if (! file_exists($composer)) {
            return $this->pass('No composer.json — skipping.');
        }

        $contents = json_decode((string) file_get_contents($composer), true) ?: [];
        $required = array_keys($contents['require'] ?? []);

        $hasSpamProtection = false;
        foreach ($required as $pkg) {
            if (in_array($pkg, ['spatie/laravel-honeypot', 'anhskohbo/no-captcha', 'brianfaust/laravel-captcha', 'arcanedev/no-captcha'], true)) {
                $hasSpamProtection = true; break;
            }
        }

        if (! $hasSpamProtection) {
            return $this->warning(
                'No honeypot / reCAPTCHA package detected in composer.json.',
                ['required' => $required]
            );
        }

        return $this->pass('A spam-protection package is installed.');
    }
}
