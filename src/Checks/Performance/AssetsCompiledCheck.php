<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;

class AssetsCompiledCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'Compiled Vite/Mix assets are present (manifest exists)'; }

    public function run()
    {
        $viteManifest   = public_path('build/manifest.json');
        $mixManifest    = public_path('mix-manifest.json');

        if (file_exists($viteManifest)) {
            $manifest = json_decode((string) file_get_contents($viteManifest), true);
            if (is_array($manifest) && ! empty($manifest)) {
                return $this->pass('Vite build manifest present.', ['assets' => count($manifest)]);
            }
        }

        if (file_exists($mixManifest)) {
            $manifest = json_decode((string) file_get_contents($mixManifest), true);
            if (is_array($manifest) && ! empty($manifest)) {
                return $this->pass('Mix manifest present.', ['assets' => count($manifest)]);
            }
        }

        return $this->warning(
            'No compiled-asset manifest found — run `npm run build` (Vite) or `npm run production` (Mix).'
        );
    }
}
