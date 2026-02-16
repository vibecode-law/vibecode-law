<?php

namespace Tests\Feature\Http\Controllers\User;

use App\Actions\User\DeleteUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed()
    {
        /** @var User */
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('user-area.profile.edit'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated()
    {
        /** @var User */
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('user-area.profile.update'), [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('user-area.profile.edit'));

        $user->refresh();

        $this->assertSame('Test', $user->first_name);
        $this->assertSame('User', $user->last_name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_profile_linkedin_url_can_be_updated()
    {
        /** @var User */
        $user = User::factory()->create([
            'linkedin_url' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('user-area.profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'linkedin_url' => 'https://www.linkedin.com/in/test-user',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('user-area.profile.edit'));

        $user->refresh();

        $this->assertSame('https://www.linkedin.com/in/test-user', $user->linkedin_url);
    }

    public function test_profile_linkedin_url_accepts_country_subdomain()
    {
        /** @var User */
        $user = User::factory()->create([
            'linkedin_url' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('user-area.profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'linkedin_url' => 'https://uk.linkedin.com/in/test-user',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('user-area.profile.edit'));

        $user->refresh();

        $this->assertSame('https://uk.linkedin.com/in/test-user', $user->linkedin_url);
    }

    public function test_profile_linkedin_url_must_be_valid_url()
    {
        /** @var User */
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('user-area.profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'linkedin_url' => 'not-a-valid-url',
            ]);

        $response->assertSessionHasErrors('linkedin_url');
    }

    public function test_profile_linkedin_url_must_start_with_linkedin_profile_prefix()
    {
        /** @var User */
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('user-area.profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'linkedin_url' => 'https://example.com/in/john-doe',
            ]);

        $response->assertSessionHasErrors('linkedin_url');
    }

    public function test_profile_linkedin_url_rejects_non_profile_linkedin_paths()
    {
        /** @var User */
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('user-area.profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'linkedin_url' => 'https://www.linkedin.com/company/acme',
            ]);

        $response->assertSessionHasErrors('linkedin_url');
    }

    public function test_profile_linkedin_url_can_be_cleared()
    {
        /** @var User */
        $user = User::factory()->create([
            'linkedin_url' => 'https://www.linkedin.com/in/old-profile',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('user-area.profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'linkedin_url' => '',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('user-area.profile.edit'));

        $user->refresh();

        $this->assertNull($user->linkedin_url);
    }

    public function test_profile_bio_can_be_updated()
    {
        /** @var User */
        $user = User::factory()->create([
            'bio' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('user-area.profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'bio' => '# Hello World\n\nThis is my **bio** with markdown.',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('user-area.profile.edit'));

        $user->refresh();

        $this->assertSame('# Hello World\n\nThis is my **bio** with markdown.', $user->bio);
    }

    public function test_profile_bio_has_html_stripped()
    {
        /** @var User */
        $user = User::factory()->create([
            'bio' => null,
        ]);

        $this
            ->actingAs($user)
            ->patch(route('user-area.profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'bio' => '<script>alert("xss")</script>Hello <b>World</b>',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('user-area.profile.edit'));

        $user->refresh();

        $this->assertSame('alert("xss")Hello World', $user->bio);
    }

    public function test_profile_bio_can_be_cleared()
    {
        /** @var User */
        $user = User::factory()->create([
            'bio' => 'Some existing bio content',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('user-area.profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'bio' => '',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('user-area.profile.edit'));

        $user->refresh();

        $this->assertNull($user->bio);
    }

    public function test_profile_bio_must_not_exceed_max_length()
    {
        /** @var User */
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('user-area.profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'bio' => str_repeat('a', 5001),
            ]);

        $response->assertSessionHasErrors('bio');
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        /** @var User */
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('user-area.profile.update'), [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('user-area.profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_handle_cannot_be_changed()
    {
        /** @var User */
        $user = User::factory()->create(['handle' => 'original-handle']);

        $this
            ->actingAs($user)
            ->patch(route('user-area.profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'handle' => 'new-handle',
                'email' => $user->email,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('user-area.profile.edit'));

        $user->refresh();

        $this->assertSame('original-handle', $user->handle);
    }

    public function test_user_can_delete_their_account()
    {
        /** @var User */
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('user-area.profile.destroy'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_delete_account_calls_delete_user_action_with_correct_user()
    {
        /** @var User */
        $user = User::factory()->create();

        $this->mock(DeleteUserAction::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('delete')
                ->once()
                ->with(\Mockery::on(function (User $argument) use ($user) {
                    return $argument->is($user);
                }));
        });

        $this
            ->actingAs($user)
            ->delete(route('user-area.profile.destroy'), [
                'password' => 'password',
            ])
            ->assertRedirect('/');
    }

    public function test_correct_password_must_be_provided_to_delete_account()
    {
        /** @var User */
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('user-area.profile.edit'))
            ->delete(route('user-area.profile.destroy'), [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect(route('user-area.profile.edit'));

        $this->assertNotNull($user->fresh());
    }
}
