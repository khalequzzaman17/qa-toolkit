<?php

/**
 * Pest test stub — pre-launch authentication assertions.
 * Copy to your project's tests/Feature/AuthTest.php and adapt the URIs.
 */

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(fn () => $this->withoutMiddleware(\Illuminate\Http\Middleware\TrustHosts::class));

it('rejects unauthenticated access to the dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('allows access after logging in', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

it('refuses login with the wrong password', function () {
    $user = User::factory()->create(['password' => Hash::make('right-password')]);

    $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertSessionHasErrors('email');
});

it('logs out and forgets the session', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/');

    $this->assertGuest();
});

it('rate-limits failed logins', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
    }

    $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertStatus(429); // Too Many Requests
});

it('requires email verification for sensitive areas', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $this->actingAs($user)
        ->get('/settings/security')
        ->assertRedirect('/verify-email'); // adjust as needed
});
