<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class WebServerConfigCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Web server docroot points to /public (not /)'; }

    public function run()
    {
        // Look for nginx/apache config files in standard locations.
        $candidates = [
            '/etc/nginx/sites-enabled/*',
            '/etc/nginx/conf.d/*.conf',
            '/etc/apache2/sites-enabled/*',
        ];

        $configs = [];
        foreach ($candidates as $pattern) {
            $configs = array_merge($configs, glob($pattern) ?: []);
        }

        foreach ($configs as $file) {
            $contents = file_get_contents($file) ?: '';
            if (str_contains($contents, base_path()) && ! str_contains($contents, base_path() . '/public')) {
                return $this->critical(
                    "Web server config '{$file}' points to base path, not /public — exposes app/ and .env.",
                    ['config' => $file]
                );
            }
        }

        return $this->pass('No misconfigured web server root found (or no access to /etc — manual verify).');
    }
}
