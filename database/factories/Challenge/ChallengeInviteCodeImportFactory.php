<?php

namespace Database\Factories\Challenge;

use App\Enums\ChallengeInviteCodeImportStatus;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\Challenge\ChallengeInviteCodeImport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChallengeInviteCodeImport>
 */
class ChallengeInviteCodeImportFactory extends Factory
{
    protected $model = ChallengeInviteCodeImport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'challenge_invite_code_id' => ChallengeInviteCode::factory(),
            'user_id' => User::factory(),
            'status' => ChallengeInviteCodeImportStatus::Pending,
            'custom_message' => null,
            'total_rows' => 0,
            'imported_count' => 0,
            'skipped_count' => 0,
            'skipped_rows' => null,
        ];
    }

    public function processing(): static
    {
        return $this->state(fn () => [
            'status' => ChallengeInviteCodeImportStatus::Processing,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => ChallengeInviteCodeImportStatus::Completed,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => ChallengeInviteCodeImportStatus::Failed,
        ]);
    }
}
