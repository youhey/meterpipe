<?php

$allowedEmails = array_filter(array_map(
    'trim',
    explode(',', (string) env('METERPIPE_ADMIN_ALLOWED_EMAILS', '')),
));

return [
    'admin_allowed_emails' => $allowedEmails,
    'admin_dev_login_enabled' => (bool) env('METERPIPE_ADMIN_DEV_LOGIN_ENABLED', false),
    'admin_dev_login_email' => env('METERPIPE_ADMIN_DEV_LOGIN_EMAIL'),
    'default_currency' => strtolower((string) env('METERPIPE_DEFAULT_CURRENCY', 'usd')),
    'monthly_budget_amount' => (float) env('METERPIPE_MONTHLY_BUDGET_AMOUNT', 0),
    'monthly_budget_currency' => strtolower((string) env('METERPIPE_MONTHLY_BUDGET_CURRENCY', 'usd')),
    'openai_collector_enabled' => (bool) env('METERPIPE_OPENAI_COLLECTOR_ENABLED', false),
    'openai_admin_key' => env('OPENAI_ADMIN_KEY'),
    'openai_cost_sync_days' => (int) env('OPENAI_COST_SYNC_DAYS', 30),
    'laravel_cloud_collector_enabled' => (bool) env('METERPIPE_LARAVEL_CLOUD_COLLECTOR_ENABLED', false),
    'laravel_cloud_api_token' => env('LARAVEL_CLOUD_API_TOKEN'),
    'laravel_cloud_cost_sync_days' => (int) env('LARAVEL_CLOUD_COST_SYNC_DAYS', 30),
    'cost_sync_queue' => env('METERPIPE_COST_SYNC_QUEUE', 'default'),
    'cost_sync_polling_interval' => env('METERPIPE_COST_SYNC_POLLING_INTERVAL', '30s'),
];
