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
    'openai_admin_key' => env('OPENAI_ADMIN_KEY'),
    'laravel_cloud_api_token' => env('LARAVEL_CLOUD_API_TOKEN'),
];
