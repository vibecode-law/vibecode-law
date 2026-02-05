<?php

namespace Database\Seeders\DemoSeeders;

use App\Models\PressCoverage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PressCoverageSeeder extends Seeder
{
    public function run(): void
    {
        // Define press coverage data with colors for placeholders
        $pressCoverageData = [
            [
                'title' => 'vibecode.law: Bridging the Gap Between Law and Technology',
                'publication_name' => 'Legal Tech News',
                'publication_date' => '2025-11-15',
                'url' => 'https://example.com/vibecode-law-bridging-gap',
                'excerpt' => 'An in-depth look at how vibecode.law is revolutionizing the way legal professionals engage with technology and share their innovations.',
                'is_published' => true,
                'display_order' => 1,
                'color' => '3b82f6', // Blue
            ],
            [
                'title' => 'The Future of Legal Practice: Community-Driven Innovation',
                'publication_name' => 'The Lawyer Magazine',
                'publication_date' => '2025-10-22',
                'url' => 'https://example.com/future-legal-practice',
                'excerpt' => 'How vibecode.law is creating a thriving community where lawyers showcase their technical projects and learn from each other.',
                'is_published' => true,
                'display_order' => 2,
                'color' => '8b5cf6', // Purple
            ],
            [
                'title' => 'Top 10 Legal Tech Platforms Every Lawyer Should Know',
                'publication_name' => 'Law Gazette',
                'publication_date' => '2025-09-08',
                'url' => 'https://example.com/top-10-legal-tech',
                'excerpt' => 'vibecode.law makes the list as an essential platform for lawyers looking to stay ahead in the digital transformation of legal services.',
                'is_published' => true,
                'display_order' => 3,
                'color' => 'f59e0b', // Amber
            ],
            [
                'title' => 'How One Platform is Transforming Legal Education',
                'publication_name' => 'Legal Innovation Today',
                'publication_date' => '2025-08-14',
                'url' => 'https://example.com/transforming-legal-education',
                'excerpt' => 'Law schools and students are turning to vibecode.law to understand the intersection of law, code, and innovation.',
                'is_published' => false,
                'display_order' => 4,
                'color' => 'ec4899', // Pink
            ],
            [
                'title' => 'Community Spotlight: vibecode.law Reaches 10,000 Users',
                'publication_name' => 'Tech & Law Review',
                'publication_date' => '2026-01-20',
                'url' => 'https://example.com/vibecode-10k-milestone',
                'excerpt' => 'Celebrating a major milestone as vibecode.law continues to grow its community of legal tech enthusiasts and innovators.',
                'is_published' => true,
                'display_order' => 5,
                'color' => '10b981', // Green
            ],
        ];

        foreach ($pressCoverageData as $data) {
            $color = $data['color'];
            unset($data['color']);

            // Create the press coverage record
            $pressCoverage = PressCoverage::create($data);

            // Download and store placeholder image
            try {
                $imageUrl = "https://placehold.co/600x600/{$color}/ffffff/png?text=" . urlencode($pressCoverage->publication_name);
                $imageContent = Http::get($imageUrl)->body();

                $directory = "press-coverage/{$pressCoverage->id}";
                Storage::disk('public')->makeDirectory($directory);
                Storage::disk('public')->put("{$directory}/thumbnail.png", $imageContent);

                $pressCoverage->thumbnail_extension = 'png';
                $pressCoverage->save();
            } catch (\Exception $e) {
                // If placeholder download fails, continue without thumbnail
            }
        }
    }
}
