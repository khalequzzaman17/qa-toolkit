<?php

/**
 * Pest test stub — pre-launch form validation assertions.
 * Copy to your project's tests/Feature/FormValidationTest.php.
 *
 * Use this template to seed validation tests for every form in your app:
 * login, register, password reset, contact, profile update, etc.
 */

use App\Models\User;
use Illuminate\Support\Str;

it('rejects empty required fields on the registration form', function () {
    $this->post('/register', [])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});

it('rejects a malformed email on registration', function () {
    $this->post('/register', [
        'name'                  => 'Jane',
        'email'                 => 'not-an-email',
        'password'              => 'Secret-123!',
        'password_confirmation' => 'Secret-123!',
    ])->assertSessionHasErrors('email');
});

it('rejects a short password on registration', function () {
    $this->post('/register', [
        'name'                  => 'Jane',
        'email'                 => 'jane@example.com',
        'password'              => 'short',
        'password_confirmation' => 'short',
    ])->assertSessionHasErrors('password');
});

it('rejects when password confirmation does not match', function () {
    $this->post('/register', [
        'name'                  => 'Jane',
        'email'                 => 'jane@example.com',
        'password'              => 'Secret-123!',
        'password_confirmation' => 'Different-456!',
    ])->assertSessionHasErrors('password');
});

it('enforces unique email on registration', function () {
    User::factory()->create(['email' => 'duplicate@example.com']);

    $this->post('/register', [
        'name'                  => 'Jane',
        'email'                 => 'duplicate@example.com',
        'password'              => 'Secret-123!',
        'password_confirmation' => 'Secret-123!',
    ])->assertSessionHasErrors('email');
});

it('accepts a fully valid registration payload', function () {
    $this->post('/register', [
        'name'                  => 'Jane Doe',
        'email'                 => 'jane@example.com',
        'password'              => 'Secret-123!',
        'password_confirmation' => 'Secret-123!',
    ])->assertRedirect('/');

    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
});
