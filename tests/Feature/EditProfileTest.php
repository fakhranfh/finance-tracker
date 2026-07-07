<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Features;

test('authenticated user can view edit profile page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/edit-profile');

    $response->assertStatus(200)
        ->assertViewIs('edit-profile')
        ->assertSee($user->name)
        ->assertSee($user->email);
});

test('unauthenticated user cannot view edit profile page', function () {
    $response = $this->get('/edit-profile');

    $response->assertRedirect('/login');
});

test('user can update profile name without changing email', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/edit-profile', [
        'name' => 'Updated Name',
        'email' => $user->email,
    ]);

    $response->assertRedirect('/edit-profile')
        ->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
    ]);
});

test('user changing email triggers verification and stores pending email', function () {
    if (! Features::enabled(Features::emailVerification())) {
        $this->markTestSkipped('Email verification feature is disabled (set EMAIL_VERIFICATION_ENABLED=true to re-enable).');
    }

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/edit-profile', [
        'name' => $user->name,
        'email' => 'newemail@example.com',
    ]);

    $response->assertRedirect('/edit-profile')
        ->assertSessionHas('pending_email_sent', 'newemail@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => $user->email,
        'pending_email' => 'newemail@example.com',
    ]);
});

test('user changing email updates it immediately when verification is disabled', function () {
    if (Features::enabled(Features::emailVerification())) {
        $this->markTestSkipped('Email verification feature is enabled.');
    }

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/edit-profile', [
        'name' => $user->name,
        'email' => 'newemail@example.com',
    ]);

    $response->assertRedirect('/edit-profile')
        ->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'newemail@example.com',
    ]);
});

test('user can upload profile photo', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension not installed');
    }

    Storage::fake('public');
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('profile.jpg', 100, 100);

    $response = $this->actingAs($user)->post('/edit-profile', [
        'name' => $user->name,
        'email' => $user->email,
        'profile_photo' => $file,
    ]);

    $response->assertRedirect('/edit-profile');
    Storage::disk('public')->assertExists('profile-photos/'.$file->hashName());
});

test('uploading a profile photo strips embedded GPS Exif metadata', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $baseJpeg = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAA/AKp//9k=');
    $exifPayload = "Exif\0\0".'FAKE_GPS_LATLON_37.7749_-122.4194';
    $exifSegment = "\xFF\xE1".pack('n', 2 + strlen($exifPayload)).$exifPayload;
    $jpegWithExif = substr($baseJpeg, 0, 2).$exifSegment.substr($baseJpeg, 2);

    $file = UploadedFile::fake()->createWithContent('profile.jpg', $jpegWithExif);

    $response = $this->actingAs($user)->post('/edit-profile', [
        'name' => $user->name,
        'email' => $user->email,
        'profile_photo' => $file,
    ]);

    $response->assertRedirect('/edit-profile');

    $storedPath = 'profile-photos/'.$file->hashName();
    Storage::disk('public')->assertExists($storedPath);

    $storedContents = Storage::disk('public')->get($storedPath);
    expect($storedContents)->not->toContain('FAKE_GPS_LATLON');
});

test('user can remove profile photo', function () {
    Storage::fake('public');
    $user = User::factory()->create(['profile_photo_path' => '/storage/profile-photos/test.jpg']);

    $response = $this->actingAs($user)->post('/edit-profile', [
        'name' => $user->name,
        'email' => $user->email,
        'remove_photo' => true,
    ]);

    $response->assertRedirect('/edit-profile');
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'profile_photo_path' => null,
    ]);
});

test('email must be unique', function () {
    $user1 = User::factory()->create(['email' => 'test@example.com']);
    $user2 = User::factory()->create();

    $response = $this->actingAs($user2)->post('/edit-profile', [
        'name' => $user2->name,
        'email' => 'test@example.com',
    ]);

    $response->assertSessionHasErrors('email');
});

test('name is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/edit-profile', [
        'name' => '',
        'email' => $user->email,
    ]);

    $response->assertSessionHasErrors('name');
});

test('email is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/edit-profile', [
        'name' => $user->name,
        'email' => '',
    ]);

    $response->assertSessionHasErrors('email');
});

test('profile photo must be valid image', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 100);

    $response = $this->actingAs($user)->post('/edit-profile', [
        'name' => $user->name,
        'email' => $user->email,
        'profile_photo' => $file,
    ]);

    $response->assertSessionHasErrors('profile_photo');
});
