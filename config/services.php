<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'rewrite_models' => array_filter(explode(',', env('GEMINI_REWRITE_MODELS', 'gemini-2.5-flash,gemini-2.0-flash'))),
    ],

    'groq' => [
        'key'   => env('GROQ_API_KEY'),
        'models' => [
            'llama-3.3-70b-versatile',
            'llama3-8b-8192',
            'mixtral-8x7b-32768',
        ],
    ],

    // ── Free AI Providers (OpenAI-compatible) ────────────────────────────────

    'mistral' => [
        'key'      => env('MISTRAL_API_KEY'),
        'base_url' => 'https://api.mistral.ai/v1/chat/completions',
        'models'   => ['open-mistral-7b', 'mistral-small-latest'],
    ],

    'cerebras' => [
        'key'      => env('CEREBRAS_API_KEY'),
        'base_url' => 'https://api.cerebras.ai/v1/chat/completions',
        'models'   => ['llama3.1-8b'],  // Fastest inference on earth
    ],

    'together' => [
        'key'      => env('TOGETHER_API_KEY'),
        'base_url' => 'https://api.together.xyz/v1/chat/completions',
        'models'   => ['meta-llama/Llama-3.3-70B-Instruct-Turbo', 'mistralai/Mixtral-8x7B-Instruct-v0.1'],
    ],

];
