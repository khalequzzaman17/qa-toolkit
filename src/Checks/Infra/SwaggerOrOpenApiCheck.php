<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class SwaggerOrOpenApiCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'API exposes /docs with OpenAPI spec (if API exists)'; }

    public function run()
    {
        $hasApi = file_exists(base_path('routes/api.php'));
        if (! $hasApi) {
            return $this->pass('No routes/api.php — API spec not required.');
        }

        $candidates = [
            base_path('openapi.json'),
            base_path('openapi.yaml'),
            base_path('swagger.json'),
            base_path('docs/openapi.json'),
            base_path('docs/openapi.yaml'),
        ];

        foreach ($candidates as $file) {
            if (file_exists($file)) {
                return $this->pass('OpenAPI spec found at ' . basename($file), ['file' => $file]);
            }
        }

        $composer = base_path('composer.json');
        if (file_exists($composer)) {
            $reqs = array_keys(json_decode((string) file_get_contents($composer), true)['require'] ?? []);
            foreach ($reqs as $pkg) {
                if (in_array($pkg, ['darkaonline/l5-swagger', 'zircote/swagger-php', 'mpociot/laravel-apidoc-generator'], true)) {
                    return $this->pass("API spec generator installed via {$pkg}.");
                }
            }
        }

        return $this->warning('No OpenAPI spec found for an existing routes/api.php — consider documenting.');
    }
}
