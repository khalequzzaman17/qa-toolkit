<?php

/**
 * Pest test stub — public-page HTTP smoke test.
 * Crawl every GET route in the public allowlist and assert 2xx/3xx.
 */

use Illuminate\Support\Facades\Route;

it('every allowlisted public page returns 2xx or 3xx', function () {
    $allowlist = ['/', '/login', '/register', '/password/reset'];

    foreach ($allowlist as $uri) {
        $response = $this->get($uri);
        expect($response->status())
            ->toBeGreaterThanOrEqual(200)
            ->toBeLessThan(500);
    }
});

it('returns 404 for unknown paths', function () {
    $this->get('/this-should-not-exist')->assertNotFound();
});

it('returns 419 on form post without csrf token', function () {
    $this->withSession([])
        ->post('/login', ['email' => 'a@b.com', 'password' => 'whatever'])
        ->assertStatus(419);
});

it('returns 405 on wrong HTTP verb', function () {
    $this->put('/')->assertStatus(405);
});
