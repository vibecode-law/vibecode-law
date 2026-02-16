<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));

        $user = User::where(column: 'email', operator: 'test@example.com')->first();
        $this->assertSame('test-user', $user->handle);
    }

    public function test_handle_is_unique_when_auto_generated()
    {
        Notification::fake();

        User::factory()->create(['handle' => 'test-user']);

        $response = $this->post(route('register.store'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));

        $user = User::where(column: 'email', operator: 'test@example.com')->first();
        $this->assertNotSame('test-user', $user->handle);
        $this->assertStringStartsWith('test-user-', $user->handle);
    }

    public function test_new_users_can_register_with_linkedin_url()
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'linkedin_url' => 'https://www.linkedin.com/in/test-user',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));

        $user = User::where(column: 'email', operator: 'test@example.com')->first();
        $this->assertSame('https://www.linkedin.com/in/test-user', $user->linkedin_url);
    }

    public function test_new_users_can_register_with_country_subdomain_linkedin_url()
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'linkedin_url' => 'https://uk.linkedin.com/in/test-user',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));

        $user = User::where(column: 'email', operator: 'test@example.com')->first();
        $this->assertSame('https://uk.linkedin.com/in/test-user', $user->linkedin_url);
    }

    public function test_linkedin_url_must_be_valid_url()
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'linkedin_url' => 'not-a-valid-url',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('linkedin_url');
        $this->assertGuest();
    }

    public function test_linkedin_url_must_start_with_linkedin_profile_prefix()
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'linkedin_url' => 'https://example.com/in/john-doe',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('linkedin_url');
        $this->assertGuest();
    }

    public function test_linkedin_url_rejects_non_profile_linkedin_paths()
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'linkedin_url' => 'https://www.linkedin.com/company/acme',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('linkedin_url');
        $this->assertGuest();
    }

    public function test_linkedin_url_can_be_empty()
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'linkedin_url' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));

        $user = User::where(column: 'email', operator: 'test@example.com')->first();
        $this->assertNull($user->linkedin_url);
    }
}
