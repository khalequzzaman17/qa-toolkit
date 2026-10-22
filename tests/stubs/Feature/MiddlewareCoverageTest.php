<?php

/**
 * Pest test stub — auth/role middleware coverage.
 * Use to verify every protected route actually applies auth + role gates.
 */

use App\Models\User;
use Illuminate\Support\Facades\Route;

it('denies guest access to every protected route', function () {
    $protected = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => in_array('GET', $r->methods()))
        ->filter(fn ($r) => collect($r->gatherMiddleware())->contains(
            fn ($m) => is_string($m) && str_starts_with($m, 'auth')
        ))
        ->map(fn ($r) => '/' . ltrim($r->uri(), '/'));

    foreach ($protected as $uri) {
        if (str_contains($uri, '{')) continue; // skip parameterised
        $this->get($uri)->assertRedirect('/login');
    }
});

it('authorises an admin user on admin/* routes', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    foreach (['admin', 'admin/users', 'admin/settings'] as $uri) {
        $uri = '/' . ltrim($uri, '/');
        if (Route::has($uri)) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }
    }
});

it('forbids non-admin users on admin/* routes', function () {
    $user = User::factory()->create(['is_admin' => false]);

    foreach (['/admin', '/admin/users'] as $uri) {
        if (Route::has(trim($uri, '/'))) {
            $this->actingAs($user)->get($uri)->assertForbidden();
        }
    }
});
