<?php

use Illuminate\Support\Facades\Artisan;
use QaToolkit\QaToolkitServiceProvider;
use QaToolkit\Reports\Report;
use QaToolkit\Support\CheckResult;

it('registers the service provider and all 120 checks', function () {
    $checks = app('qa-toolkit.checks');

    expect($checks)->toBeArray()
        ->and(count($checks))->toBe(120);
});

it('registers the report singleton in container', function () {
    $report = app(Report::class);

    expect($report)->toBeInstanceOf(Report::class);
});

it('runs qa:checks command successfully and lists checks', function () {
    $exit = Artisan::call('qa:checks');

    expect($exit)->toBe(0);
    $output = Artisan::output();
    expect($output)->toContain('Suite')
        ->and($output)->toContain('Label')
        ->and($output)->toContain('Class');
});

it('runs qa:run command without throwing exceptions', function () {
    $exit = Artisan::call('qa:run');

    expect($exit)->toBeIn([0, 1]);
    $output = Artisan::output();
    expect($output)->toContain('QA Toolkit · pre-launch sweep');
});

it('runs only security suite with --suite=security', function () {
    $exit = Artisan::call('qa:run', ['--suite' => 'security']);

    expect($exit)->toBeIn([0, 1]);
    $output = Artisan::output();
    expect($output)->toContain('QA Toolkit · pre-launch sweep')
        ->and($output)->toContain('APP_KEY');
});

it('generates a json report file via qa:report command', function () {
    $path = tempnam(sys_get_temp_dir(), 'qa_report_') . '.json';
    @unlink($path);

    $exit = Artisan::call('qa:report', [
        '--format' => 'json',
        '--path'   => $path,
    ]);

    expect($exit)->toBeIn([0, 1])
        ->and(file_exists($path))->toBeTrue();

    $json = json_decode((string) file_get_contents($path), true);
    expect($json)->toBeArray()
        ->and($json)->toHaveKeys(['summary', 'worst_severity', 'results']);

    @unlink($path);
});

it('generates a markdown report file via qa:report command', function () {
    $path = tempnam(sys_get_temp_dir(), 'qa_report_') . '.md';
    @unlink($path);

    $exit = Artisan::call('qa:report', [
        '--format' => 'markdown',
        '--path'   => $path,
    ]);

    expect($exit)->toBeIn([0, 1])
        ->and(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)->toContain('# QA Report');

    @unlink($path);
});

it('honors fail_on threshold=none to exit zero', function () {
    config()->set('qa-toolkit.fail_on', 'none');

    $exit = Artisan::call('qa:run');
    expect($exit)->toBe(0);
});

it('correctly calculates report severities and thresholds', function () {
    $report = new Report();

    $report->add(CheckResult::pass('security', 'app_key_set', 'Key is set'));
    $report->add(CheckResult::warning('security', 'debug_mode', 'Debug mode warning'));

    expect($report->count())->toBe(2)
        ->and($report->worstSeverity())->toBe('warning')
        ->and($report->failsOn('critical'))->toBeFalse()
        ->and($report->failsOn('warning'))->toBeTrue()
        ->and($report->failsOn('none'))->toBeFalse();

    $report->add(CheckResult::critical('security', 'strict_mode', 'Critical error'));
    expect($report->worstSeverity())->toBe('critical')
        ->and($report->failsOn('critical'))->toBeTrue();
});
