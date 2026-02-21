<?php

namespace Database\Seeders\DemoSeeders;

use App\Enums\TagType;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * @var array<int, array{title: string, slug: string, tagline: string, description: string, learning_objectives: string, order: int, experience_level: \App\Enums\ExperienceLevel, duration_seconds: int, allow_preview: bool, is_featured: bool, publish_date: string, lessons: array<int, array{title: string, slug: string, tagline: string, description: string, learning_objectives: string, asset_id: string, playback_id: string, host: \App\Enums\VideoHost, duration_seconds: int, gated: bool, order: int}>}>
     */
    protected array $courseData;

    public function __construct()
    {
        $this->loadCourseData();
    }

    protected function loadCourseData(): void
    {
        /** @var array<int, array{title: string, slug: string, tagline: string, description: string, learning_objectives: string, order: int, experience_level: \App\Enums\ExperienceLevel, duration_seconds: int, allow_preview: bool, is_featured: bool, publish_date: string, lessons: array<int, array{title: string, slug: string, tagline: string, description: string, learning_objectives: string, asset_id: string, playback_id: string, host: \App\Enums\VideoHost, duration_seconds: int, gated: bool, order: int}>}> $data */
        $data = require database_path('data/courses.php');
        $this->courseData = $data;
    }

    public function run(): void
    {
        $tagsByType = collect(TagType::cases())
            ->mapWithKeys(fn (TagType $type) => [$type->value => Tag::query()->where('type', $type)->pluck('id')])
            ->filter(fn ($ids) => $ids->isNotEmpty());

        foreach ($this->courseData as $data) {
            $lessons = $data['lessons'];
            unset($data['lessons']);

            $course = Course::factory()->withStockThumbnail()->create($data);

            $course->tags()->attach($tagsByType->map->random());

            $instructor = User::factory()->create();

            foreach ($lessons as $lessonData) {
                $lesson = Lesson::factory()->withStockThumbnail()->create([
                    ...$lessonData,
                    'course_id' => $course->id,
                ]);

                $lesson->instructors()->attach($instructor);
                $lesson->tags()->attach($tagsByType->map->random());
            }
        }
    }
}
