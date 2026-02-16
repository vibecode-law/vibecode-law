<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Notification::fake();
});

it('allows new user to upload avatar during registration', function () {
    post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'avatar' => UploadedFile::fake()->image('avatar.jpg', 100, 100),
    ])->assertRedirect();

    $user = User::where('email', 'test@example.com')->first();

    expect($user->avatar_path)->not()->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});

it('stores registration avatar in users/avatars directory', function () {
    post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'avatar' => UploadedFile::fake()->image('avatar.png', 100, 100),
    ])->assertRedirect();

    $user = User::where('email', 'test@example.com')->first();

    expect($user->avatar_path)->toStartWith('users/avatars/');
    expect($user->avatar_path)->toEndWith('.png');
});

it('allows registration without avatar', function () {
    post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect();

    $user = User::where('email', 'test@example.com')->first();

    expect($user->avatar_path)->toBeNull();
});

it('validates registration avatar is an image', function () {
    post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'avatar' => UploadedFile::fake()->create('document.pdf', 100),
    ])->assertSessionHasErrors('avatar');
});

it('validates registration avatar max size of 2MB', function () {
    post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'avatar' => UploadedFile::fake()->image('large-avatar.jpg')->size(3000),
    ])->assertSessionHasErrors('avatar');
});

it('accepts valid image types during registration', function (string $extension) {
    post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => "test-{$extension}@example.com",
        'password' => 'password',
        'password_confirmation' => 'password',
        'avatar' => UploadedFile::fake()->image("avatar.{$extension}", 100, 100),
    ])->assertSessionHasNoErrors();

    $user = User::where('email', "test-{$extension}@example.com")->first();
    expect($user->avatar_path)->not()->toBeNull();
})->with([
    'jpg',
    'jpeg',
    'png',
    'gif',
    'webp',
]);
