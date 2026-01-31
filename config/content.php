<?php

return [
    'default_meta_description' => 'vibecode.law is a community-driven platform where legal technology innovators showcase their vibecode projects, discover new tools, and connect with like-minded builders transforming the legal industry.',

    'legal' => [
        [
            'title' => 'Community Guidelines',
            'slug' => 'community-guidelines',
        ],
        [
            'title' => 'Terms of Use',
            'slug' => 'terms-of-use',
        ],
        [
            'title' => 'Privacy Notice',
            'slug' => 'privacy-notice',
        ],
    ],

    'about' => [
        'index' => [
            'title' => 'About vibecode.law',
            'slug' => null,
        ],
        'children' => [
            [
                'title' => 'The Community',
                'slug' => 'the-community',
                'summary' => 'Meet the community that makes vibecode.law possible.',
                'icon' => 'heart',
                'dynamic' => true,
            ],
            [
                'title' => 'Submission Process',
                'slug' => 'submission-process',
                'summary' => 'Learn how to submit your legal tech project to the showcase and share your creation with the community.',
                'icon' => 'rocket',
            ],
            [
                'title' => 'Moderation Process',
                'slug' => 'moderation-process',
                'summary' => 'Learn more about our moderation process, who is involved and how to raise concerns.',
                'icon' => 'shield',
            ],
            [
                'title' => 'Responsible Vibecoding',
                'slug' => 'responsible-vibecoding',
                'summary' => 'Ground rules for building legal tech responsibly with AI — protecting data, being honest, and thinking about impact.',
                'icon' => 'scale',
            ],
            [
                'title' => 'Contact',
                'slug' => 'contact',
                'summary' => 'Get in touch with us for questions, feedback, partnership opportunities, or to get involved.',
                'icon' => 'mail',
            ],
        ],
    ],

    'resources' => [
        'index' => [
            'title' => 'Resources',
            'slug' => null,
        ],
        'children' => [
            [
                'title' => 'What is Vibecoding?',
                'slug' => 'what-is-vibecoding',
                'summary' => 'Discover what vibecoding is and how AI-assisted development is transforming the way legal professionals build software.',
                'icon' => 'lightbulb',
            ],
            [
                'title' => 'Start Vibecoding',
                'slug' => 'start-vibecoding',
                'summary' => 'A practical guide to choosing platforms and tools to begin your vibecoding journey in legal tech.',
                'icon' => 'play',
            ],
            [
                'title' => 'Risks of Vibecoding',
                'slug' => 'risks-of-vibecoding',
                'summary' => 'Understand the technical, security, and professional risks of AI-generated code and how to mitigate them.',
                'icon' => 'alert-triangle',
            ],
        ],
    ],
];
