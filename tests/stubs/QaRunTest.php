<?php

/**
 * Pest test stub — covers the QA Toolkit's own `qa:run` command.
 *
 * Publish this file to your Laravel app with:
 *   php artisan vendor:publish --tag=qa-toolkit-stubs
 * Then run: `php pest tests/QaToolkitStubs/QaRunTest.php`
 */

use Illuminate\Support\Facades\Artisan;

it('runs the full QA sweep without throwing', function () {
    $exit = Artisan::call('qa:run');
    expect($exit)->toBeIn([0, 1]); // pass or non-zero (failures) but never crash
});

it('emits a JSON report when --format=json is set', function () {
    $path = storage_path('app/qa-test.json');
    @unlink($path);

    Artisan::call("qa:report --format=json --path={$path}");

    expect(file_exists($path))->toBeTrue();
    $json = json_decode((string) file_get_contents($path), true);
    expect($json)->toBeArray()
        ->and($json)->toHaveKeys(['summary', 'worst_severity', 'results']);
});

it('runs only the security suite with --suite=security', function () {
    Artisan::call('qa:run --suite=security');
    $output = Artisan::output();
    expect($output)->toContain('Security suite');
});

it('honors the configured fail_on threshold', function () {
    config()->set('qa-toolkit.fail_on', 'none');
    $exit = Artisan::call('qa:run');
    expect($exit)->toBe(0); // never fail when threshold is "none"
});
