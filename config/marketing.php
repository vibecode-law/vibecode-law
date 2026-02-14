<?php

return [
    'enabled' => env('MARKETING_ENABLED', false),

    'newsletter_template_uuid' => env('MARKETING_NEWSLETTER_TEMPLATE_UUID', 'test-newsletter-template-uuid'),

    'main_list_uuid' => env('MARKETING_MAIN_LIST_UUID', 'test-main-list-uuid'),

    'has_showcase_tag_uuid' => env('MARKETING_HAS_SHOWCASE_TAG_UUID', 'test-has-showcase-tag-uuid'),

    'is_user_tag_uuid' => env('MARKETING_IS_USER_TAG_UUID', 'test-is-user-tag-uuid'),
];
