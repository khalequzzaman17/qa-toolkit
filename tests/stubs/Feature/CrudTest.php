<?php

/**
 * Pest test stub — pre-launch CRUD resource assertions.
 * Replace "Post" with your actual model + routes.
 *
 * Covers the seven resource verbs that you declared in routes/web.php.
 */

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;

it('lists posts on the index page', function () {
    Post::factory()->count(3)->create();

    $this->actingAs(User::factory()->create())
        ->get('/posts')
        ->assertOk()
        ->assertSee(Post::first()->title);
});

it('shows the create form', function () {
    $this->actingAs(User::factory()->create())
        ->get('/posts/create')
        ->assertOk()
        ->assertSee('@csrf', false);
});

it('stores a valid post', function () {
    $this->actingAs($user = User::factory()->create())
        ->post('/posts', [
            'title'   => 'Hello world',
            'body'    => 'Lorem ipsum dolor',
        ])
        ->assertRedirect('/posts');

    $this->assertDatabaseHas('posts', [
        'title'    => 'Hello world',
        'user_id'  => $user->id,
    ]);
});

it('rejects creating a post with missing fields', function () {
    $this->actingAs(User::factory()->create())
        ->post('/posts', [])
        ->assertSessionHasErrors(['title', 'body']);
});

it('shows a single post', function () {
    $post = Post::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get("/posts/{$post->id}")
        ->assertOk()
        ->assertSee($post->title);
});

it('shows the edit form', function () {
    $post = Post::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get("/posts/{$post->id}/edit")
        ->assertOk();
});

it('updates a post', function () {
    $post = Post::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put("/posts/{$post->id}", ['title' => 'Updated', 'body' => 'Updated body'])
        ->assertRedirect("/posts/{$post->id}");

    expect($post->fresh()->title)->toBe('Updated');
});

it('deletes a post', function () {
    $post = Post::factory()->create();

    $this->actingAs(User::factory()->create())
        ->delete("/posts/{$post->id}")
        ->assertRedirect('/posts');

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

it('forbids unauthenticated users from creating posts', function () {
    $this->post('/posts', [])->assertRedirect('/login');
});

it('rejects HTML in title (XSS)', function () {
    $this->actingAs(User::factory()->create())
        ->post('/posts', ['title' => '<script>alert(1)</script>', 'body' => 'x'])
        ->assertSessionHasErrors('title');
});
