<?php

namespace Database\Seeders\DemoSeeders;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestimonialsSeeder extends Seeder
{
    public function run(): void
    {
        // Get some random users for user-linked testimonials
        $users = User::inRandomOrder()->limit(2)->get();

        // Mix of user-linked and standalone testimonials
        $testimonials = [
            // User-linked testimonial (pulls name, job, org, avatar from user)
            [
                'user_id' => $users[0]->id ?? null,
                'content' => 'vibecode.law has been invaluable for staying current with legal tech developments. The community insights and resources have helped me streamline my research process significantly.',
                'is_published' => true,
                'display_order' => 1,
            ],
            // Another user-linked testimonial
            [
                'user_id' => $users[1]->id ?? null,
                'content' => 'An essential resource for any legal professional looking to understand the intersection of law and technology. The quality of content and community engagement is outstanding.',
                'is_published' => true,
                'display_order' => 2,
            ],
            // Standalone testimonials (with their own data)
            [
                'name' => 'Emma Thompson',
                'job_title' => 'Legal Tech Consultant',
                'organisation' => 'LegalTech Solutions Ltd',
                'content' => 'I recommend vibecode.law to all my clients. It\'s become the go-to platform for understanding how technology is transforming legal practice.',
                'avatar_path' => 'https://ui-avatars.com/api/?name=Emma+Thompson&background=8b5cf6&color=fff&size=128',
                'is_published' => true,
                'display_order' => 3,
            ],
            [
                'name' => 'Michael Rodriguez',
                'job_title' => 'Trainee Solicitor',
                'organisation' => 'Freshfields',
                'content' => 'The showcase section is incredibly inspiring. Seeing what other legal professionals are building helps me understand where the industry is heading.',
                'avatar_path' => 'https://ui-avatars.com/api/?name=Michael+Rodriguez&background=ec4899&color=fff&size=128',
                'is_published' => true,
                'display_order' => 4,
            ],
            [
                'name' => 'Priya Patel',
                'job_title' => 'Legal Operations Manager',
                'organisation' => 'Linklaters',
                'content' => 'vibecode.law bridges the gap between legal expertise and technical innovation. The community here truly understands both worlds.',
                'avatar_path' => 'https://ui-avatars.com/api/?name=Priya+Patel&background=10b981&color=fff&size=128',
                'is_published' => true,
                'display_order' => 5,
            ],
            [
                'name' => 'David Foster',
                'job_title' => 'General Counsel',
                'organisation' => 'Tech Innovations Corp',
                'content' => 'This platform has helped our legal team embrace automation and improve efficiency. The resources and community support are exceptional.',
                'avatar_path' => 'https://ui-avatars.com/api/?name=David+Foster&background=ef4444&color=fff&size=128',
                'is_published' => false,
                'display_order' => 6,
            ],
            [
                'name' => 'Lisa Anderson',
                'job_title' => 'Law Student',
                'organisation' => 'Oxford University',
                'content' => 'As a law student interested in legal tech, vibecode.law has given me insights into the future of legal practice. Absolutely brilliant resource!',
                'avatar_path' => 'https://ui-avatars.com/api/?name=Lisa+Anderson&background=06b6d4&color=fff&size=128',
                'is_published' => true,
                'display_order' => 7,
            ],
        ];

        foreach ($testimonials as $testimonialData) {
            Testimonial::create($testimonialData);
        }
    }
}
