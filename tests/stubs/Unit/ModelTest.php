<?php

/**
 * Pest test stub — unit tests for Eloquent models.
 * Use as a base for per-model coverage: scopes, casts, fillable, relations.
 */

use App\Models\Post;
use App\Models\User;

it('allows mass assignment only on whitelisted attributes', function () {
    $post = new Post();
    expect($post->getFillable())->toBeArray()->not->toBeEmpty();
});

it('casts dates correctly', function () {
    $post = Post::factory()->create();
    expect($post->published_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('hides sensitive attributes from toArray', function () {
    $user = User::factory()->create();
    $array = $user->toArray();
    expect($array)->not->toHaveKey('password')
        ->and($array)->not->toHaveKey('remember_token');
});

it('exposes a working scope', function () {
    Post::factory()->count(3)->create(['is_published' => true]);
    Post::factory()->create(['is_published' => false]);

    expect(Post::published()->count())->toBe(3);
});

it('belongs to the expected owner', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    expect($post->user)->not->toBeNull()
        ->and($post->user->is($user))->toBeTrue();
});
