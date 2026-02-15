<?php

namespace Database\Seeders;

use Database\Seeders\DemoSeeders\ChallengeSeeder;
use Database\Seeders\DemoSeeders\CourseSeeder;
use Database\Seeders\DemoSeeders\PracticeAreasSeeder;
use Database\Seeders\DemoSeeders\PressCoverageSeeder;
use Database\Seeders\DemoSeeders\ShowcaseSeeder;
use Database\Seeders\DemoSeeders\TestimonialsSeeder;
use Database\Seeders\DemoSeeders\UsersSeeder;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DatabaseSeeder::class,
            UsersSeeder::class,
            PracticeAreasSeeder::class,
            ShowcaseSeeder::class,
            TestimonialsSeeder::class,
            PressCoverageSeeder::class,
            ChallengeSeeder::class,
            CourseSeeder::class,
        ]);
    }
}
