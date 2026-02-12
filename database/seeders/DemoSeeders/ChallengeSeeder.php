<?php

namespace Database\Seeders\DemoSeeders;

use App\Models\Challenge\Challenge;
use App\Models\Organisation\Organisation;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class ChallengeSeeder extends Seeder
{
    protected Collection $approvedShowcases;

    /**
     * @var array<int, array{title: string, tagline: string, description: string, organisation: array{name: string, tagline: string, about: string}}>
     */
    protected array $challengeData;

    public function __construct()
    {
        $this->approvedShowcases = Showcase::query()
            ->approved()
            ->get();

        $this->loadAndShuffleChallengeData();
    }

    protected function loadAndShuffleChallengeData(): void
    {
        /** @var array<int, array{title: string, tagline: string, description: string, organisation: array{name: string, tagline: string, about: string}}> $data */
        $data = require database_path('data/challenges.php');
        $this->challengeData = collect($data)->shuffle()->all();
    }

    /**
     * @return array{title: string, tagline: string, description: string, organisation: array{name: string, tagline: string, about: string}}|null
     */
    protected function getNextChallengeData(): ?array
    {
        if (count($this->challengeData) === 0) {
            return null;
        }

        return array_pop($this->challengeData);
    }

    public function run(): void
    {
        // Create featured ongoing challenges
        $this->createChallenge(
            count: 2,
            ongoing: true,
            featured: true
        );

        // Create ongoing challenges (started, not yet ended)
        $this->createChallenge(
            count: 2,
            ongoing: true
        );

        // Create challenges ending soon
        $this->createChallenge(
            count: 1,
            endingSoon: true
        );

        // Create recently ended challenges
        $this->createChallenge(
            count: 1,
            ended: true
        );

        // Create upcoming challenges (not yet started)
        $this->createChallenge(
            count: 1,
            upcoming: true
        );
    }

    protected function createChallenge(
        int $count = 1,
        bool $ongoing = false,
        bool $endingSoon = false,
        bool $ended = false,
        bool $upcoming = false,
        bool $featured = false
    ): void {
        for ($i = 1; $i <= $count; $i++) {
            $data = $this->getNextChallengeData();

            if ($data === null) {
                return;
            }

            $slugBase = preg_replace(pattern: '/[^a-zA-Z\s]/', replacement: '', subject: $data['title']);
            $slug = Str::slug($slugBase).'-'.fake()->lexify('???');

            $challengeAttributes = [
                'title' => $data['title'],
                'slug' => $slug,
                'tagline' => $data['tagline'],
                'description' => $data['description'],
            ];

            // Featured challenges always have an organisation for better display
            // Others randomly assign to either an organisation or a user
            if ($featured === true || fake()->boolean()) {
                $organisation = Organisation::factory()->withStockThumbnail()->create([
                    'name' => $data['organisation']['name'],
                    'tagline' => $data['organisation']['tagline'],
                    'about' => $data['organisation']['about'],
                ]);
                $challengeAttributes['organisation_id'] = $organisation->id;
            } else {
                $user = User::factory()->create();
                $challengeAttributes['user_id'] = $user->id;
            }

            if ($ongoing === true) {
                $challengeAttributes['is_active'] = true;
                $challengeAttributes['starts_at'] = Date::now()->subDays(random_int(14, 45));
                $challengeAttributes['ends_at'] = Date::now()->addDays(random_int(14, 60));
            }

            if ($endingSoon === true) {
                $challengeAttributes['is_active'] = true;
                $challengeAttributes['starts_at'] = Date::now()->subDays(random_int(30, 60));
                $challengeAttributes['ends_at'] = Date::now()->addDays(random_int(3, 10));
            }

            if ($ended === true) {
                $challengeAttributes['is_active'] = false;
                $challengeAttributes['starts_at'] = Date::now()->subDays(random_int(60, 120));
                $challengeAttributes['ends_at'] = Date::now()->subDays(random_int(7, 30));
            }

            if ($upcoming === true) {
                $challengeAttributes['is_active'] = true;
                $challengeAttributes['starts_at'] = Date::now()->addDays(random_int(7, 30));
                $challengeAttributes['ends_at'] = Date::now()->addDays(random_int(60, 120));
            }

            if ($featured === true) {
                $challengeAttributes['is_featured'] = true;
            }

            $challenge = Challenge::factory()->withStockThumbnail()->create($challengeAttributes);

            // Attach some showcases to ended and ongoing challenges
            if (($ended === true || $ongoing === true) && $this->approvedShowcases->isNotEmpty()) {
                $showcasesToAttach = $this->approvedShowcases
                    ->random(min($this->approvedShowcases->count(), random_int(2, 6)));

                $challenge->showcases()->sync($showcasesToAttach->pluck('id'));
            }
        }
    }
}
