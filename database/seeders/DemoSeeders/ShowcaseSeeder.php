<?php

namespace Database\Seeders\DemoSeeders;

use App\Enums\ShowcaseStatus;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseImage;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\Showcase\ShowcaseFactory;
use Database\Factories\Showcase\ShowcaseImageFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Lottery;
use Illuminate\Support\Str;

class ShowcaseSeeder extends Seeder
{
    protected Collection $users;

    protected Collection $practiceAreas;

    /**
     * @var array<int, array{title: string, tagline: string, description: string, key_features: string, help_needed: string, user: array{first_name: string, last_name: string, job_title: string, organisation: string, bio: string}}>
     */
    protected array $showcaseData;

    public function __construct()
    {
        $this->users = User::all();
        $this->practiceAreas = PracticeArea::all();
        $this->loadAndShuffleShowcaseData();
    }

    protected function loadAndShuffleShowcaseData(): void
    {
        /** @var array<int, array{title: string, tagline: string, description: string, key_features: string, help_needed: string, user: array{first_name: string, last_name: string, job_title: string, organisation: string, bio: string}}> $data */
        $data = require database_path('data/showcases.php');
        $this->showcaseData = collect($data)->shuffle()->all();
    }

    /**
     * @return array{title: string, tagline: string, description: string, key_features: string, help_needed: string, user: array{first_name: string, last_name: string, job_title: string, organisation: string, bio: string}}|null
     */
    protected function getNextShowcaseData(): ?array
    {
        if (count($this->showcaseData) === 0) {
            return null;
        }

        return array_pop($this->showcaseData);
    }

    public function run(): void
    {
        // Submitted this month
        $this->createShowcase(
            count: 7,
            state: ShowcaseStatus::Approved,
            submittedDate: Date::now(),
            featured: false
        );

        // Submitted last month
        $this->createShowcase(
            count: 6,
            state: ShowcaseStatus::Approved,
            submittedDate: Date::now()->subMonthsNoOverflow(1),
            featured: false
        );

        // Submitted two months ago
        $this->createShowcase(
            count: 4,
            state: ShowcaseStatus::Approved,
            submittedDate: Date::now()->subMonthsNoOverflow(2),
            featured: false
        );

        // Submitted three months ago
        $this->createShowcase(
            count: 5,
            state: ShowcaseStatus::Approved,
            submittedDate: Date::now()->subMonthsNoOverflow(3),
            featured: false
        );

        Showcase::where('status', ShowcaseStatus::Approved)->take(3)->update([
            'is_featured' => true,
        ]);
    }

    protected function createShowcase(int $count = 1, ShowcaseStatus $state = ShowcaseStatus::Approved, ?Carbon $submittedDate = null, bool $featured = false): void
    {
        $approved = $state === ShowcaseStatus::Approved;

        if ($approved === true) {
            $submittedDate ??= Date::now();
        }

        for ($i = 1; $i <= $count; $i++) {
            $data = $this->getNextShowcaseData();

            $showcaseAttributes = [
                'submitted_date' => $submittedDate,
                'approved_at' => $submittedDate?->copy()->addDays(random_int(1, 7)),
            ];

            // If we have preset data, use it
            if ($data !== null) {
                $slugBase = preg_replace(pattern: '/[^a-zA-Z\s]/', replacement: '', subject: $data['title']);
                $slug = Str::slug($slugBase).'-'.fake()->lexify('???');

                $user = User::factory()->create([
                    'first_name' => $data['user']['first_name'],
                    'last_name' => $data['user']['last_name'],
                    'job_title' => $data['user']['job_title'],
                    'organisation' => $data['user']['organisation'],
                    'bio' => $data['user']['bio'],
                ]);

                $showcaseAttributes = array_merge($showcaseAttributes, [
                    'user_id' => $user->id,
                    'title' => $data['title'],
                    'slug' => $slug,
                    'tagline' => $data['tagline'],
                    'description' => $data['description'],
                    'key_features' => $data['key_features'],
                    'help_needed' => $data['help_needed'],
                ]);
            }

            $showcase = Showcase::factory()
                ->withoutPracticeAreas()
                ->when($approved, fn (ShowcaseFactory $factory) => $factory->approved())
                ->when($state === ShowcaseStatus::Rejected, fn (ShowcaseFactory $factory) => $factory->rejected())
                ->when($state === ShowcaseStatus::Pending, fn (ShowcaseFactory $factory) => $factory->pending())
                ->when($state === ShowcaseStatus::Draft, fn (ShowcaseFactory $factory) => $factory->draft())
                ->when($featured, fn (ShowcaseFactory $factory) => $factory->featured())
                ->when(
                    Lottery::odds(70, 100)->choose(),
                    fn (ShowcaseFactory $factory) => $factory->withStockThumbnail()
                )
                ->has(
                    ShowcaseImage::factory()
                        ->count(3)
                        ->when($approved, fn (ShowcaseImageFactory $factory) => $factory->withStockImage()),
                    'images'
                )
                ->create($showcaseAttributes);

            $upvoters = User::factory()->count(random_int(1, 50))->recycle($this->users)->create();

            $showcase->upvoters()->sync($upvoters->pluck('id'));

            $practiceAreas = $this->practiceAreas->random(random_int(1, 3));
            $showcase->practiceAreas()->sync($practiceAreas->pluck('id'));
        }
    }
}
