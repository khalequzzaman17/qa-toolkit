<?php

/**
 * Pest configuration for the QA Toolkit's own test suite.
 *
 * When you publish the stubs into your app with:
 *   php artisan vendor:publish --tag=qa-toolkit-stubs
 * Pest will pick them up automatically (no extra Pest config needed).
 */

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)
    ->in('Feature', 'Unit');
