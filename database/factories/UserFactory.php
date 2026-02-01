<?php

namespace Database\Factories;

use App\Enums\TeamType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'handle' => Str::slug("$firstName $lastName ".random_int(100000, 999999)),
            'organisation' => fake()->company(),
            'job_title' => fake()->jobTitle(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_admin' => false,
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'avatar_path' => null,
            'linkedin_url' => 'https://www.linkedin.com/in/'.fake()->slug(),
            'bio' => fake()->paragraphs(asText: true),
            'blocked_from_submissions_at' => null,
            'team_type' => null,
            'team_role' => null,
            'team_order' => null,
            'marketing_opt_out_at' => null,
            'external_subscriber_uuid' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the user is an administrator.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }

    /**
     * Indicate that the user is a moderator.
     */
    public function moderator(): static
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            $user->assignRole('Moderator');
        });
    }

    /**
     * Indicate that the user is blocked from submissions.
     */
    public function blockedFromSubmissions(): static
    {
        return $this->state(fn (array $attributes) => [
            'blocked_from_submissions_at' => now(),
        ]);
    }

    /**
     * Indicate that the user is a core team member.
     */
    public function coreTeam(?string $role = null, ?int $order = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'team_type' => TeamType::CoreTeam,
            'team_role' => $role ?? fake()->jobTitle(),
            'team_order' => $order,
        ]);
    }

    /**
     * Indicate that the user is a collaborator.
     */
    public function collaborator(?string $role = null, ?int $order = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'team_type' => TeamType::Collaborator,
            'team_role' => $role ?? fake()->jobTitle(),
            'team_order' => $order,
        ]);
    }

    /**
     * Indicate that the user has opted out of marketing emails.
     */
    public function marketingOptedOut(): static
    {
        return $this->state(fn (array $attributes): array => [
            'marketing_opt_out_at' => now(),
        ]);
    }
}
