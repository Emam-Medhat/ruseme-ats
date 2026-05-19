<?php

return [

    'name' => env('APP_BRAND_NAME', 'CV Genius AI'),

    'tagline' => 'ATS-grade resume intelligence powered by AI',

    'supported_locales' => ['en', 'ar'],

    'default_locale' => env('APP_LOCALE', 'en'),

    'plans' => [
        'free' => [
            'name' => 'Free',
            'credits' => 3,
            'features' => ['basic_analysis', 'pdf_upload'],
        ],
        'pro' => [
            'name' => 'Pro',
            'credits' => 50,
            'price_monthly' => 19,
            'features' => ['full_analysis', 'job_match', 'pdf_download', 'rewrites'],
        ],
        'team' => [
            'name' => 'Team',
            'credits' => 200,
            'price_monthly' => 49,
            'features' => ['team_seats', 'admin_analytics', 'university_license'],
        ],
    ],

    'upload' => [
        'max_kb' => 5120,
        'mimes' => ['pdf', 'docx'],
    ],

];
