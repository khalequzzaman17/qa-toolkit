<?php

/**
 * Pest test stub — file upload validation.
 * Copy to your project's tests/Feature/FileUploadTest.php.
 */

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('rejects files larger than the configured max size', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('big.pdf', 20000); // 20MB

    $this->actingAs(User::factory()->create())
        ->post('/uploads', ['file' => $file])
        ->assertSessionHasErrors('file');
});

it('rejects files with disallowed extensions', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('payload.exe', 100);

    $this->actingAs(User::factory()->create())
        ->post('/uploads', ['file' => $file])
        ->assertSessionHasErrors('file');
});

it('stores an allowed file in the configured disk', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('photo.jpg', 640, 480);

    $this->actingAs(User::factory()->create())
        ->post('/uploads', ['file' => $file])
        ->assertRedirect();

    Storage::disk('public')->assertExists('uploads/' . $file->hashName());
});

it('does not allow uploads without authentication', function () {
    $file = UploadedFile::fake()->image('x.jpg');

    $this->post('/uploads', ['file' => $file])->assertRedirect('/login');
});
