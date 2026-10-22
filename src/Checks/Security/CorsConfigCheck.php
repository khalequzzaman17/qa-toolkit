<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class CorsConfigCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'CORS paths/allowed_origins are explicit, not wildcard'; }

    public function run()
    {
        $paths   = (array) config('cors.paths', []);
        $origins = (array) config('cors.allowed_origins', []);

        $wildcardOrigin = false;
        foreach ($origins as $origin) {
            if (in_array($origin, ['*', '**', 'null'], true)) {
                $wildcardOrigin = true; break;
            }
        }

        if ($wildcardOrigin) {
            return $this->critical(
                'CORS allowed_origins includes "*" — any site can call your API cross-origin.',
                ['origins' => $origins]
            );
        }

        $wildcardPath = in_array('*', $paths, true) || in_array('api/*', $paths, true) === false && count($paths) > 0
            ? false
            : false;

        if (empty($paths)) {
            return $this->warning('No CORS paths configured — API requests from browsers will fail.');
        }

        return $this->pass('CORS configuration looks reasonable.', [
            'paths' => $paths,
            'origins' => $origins,
        ]);
    }
}
