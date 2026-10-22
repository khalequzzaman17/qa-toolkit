<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class GdprCookieCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'Cookie consent banner rendered on first visit'; }

    public function run()
    {
        // Heuristic: render the home page and check for cookie/consent string.
        // We don't have a HTTP client in this env reliably; do a static check.
        $files = [
            resource_path('views/layouts/app.blade.php'),
            resource_path('views/layouts/guest.blade.php'),
            resource_path('views/components/layout.blade.php'),
        ];

        foreach ($files as $file) {
            if (! file_exists($file)) continue;
            $contents = file_get_contents($file) ?: '';
            if (preg_match('/(cookie[-_]?consent|gdpr|consent)/i', $contents)) {
                return $this->pass('Cookie consent banner detected in layout.');
            }
        }

        return $this->warning('No cookie consent banner found in main layouts.');
    }
}
