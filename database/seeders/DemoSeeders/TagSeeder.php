<?php

namespace Database\Seeders\DemoSeeders;

use App\Enums\TagType;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<value-of<TagType>, list<string>> $tags */
        $tags = [
            TagType::Tool->value => [
                'Google AI Studio',
                'Lovable',
                'Replit',
                'Claude Code',
            ],
            TagType::Skill->value => [
                'Backend',
                'Frontend',
                'Design',
                'UX',
                'Prompting',
            ],
            TagType::TechStack->value => [
                'Next.js',
                'React',
                'Vue',
                'Laravel',
                'Ruby on Rails',
            ],
        ];

        foreach ($tags as $type => $names) {
            foreach ($names as $name) {
                Tag::factory()->create([
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'type' => $type,
                ]);
            }
        }
    }
}
