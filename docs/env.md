# Environment

root `.env` は Docker Compose 用、`src/.env` は Laravel 用です。

## root `.env`

- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DB_ROOT_PASSWORD`
- `FORWARD_MYSQL_PORT`
- `FORWARD_NGINX_PORT`
- `FORWARD_VALKEY_PORT`
- `FORWARD_MINIO_SERVICE_PORT`
- `FORWARD_MINIO_CONSOLE_PORT`

root `.env` は Docker Compose の port forwarding と middleware bootstrap values 用です。Laravel application settings は `src/.env` に置きます。

## `src/.env`

- `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION`
- `REDIS_HOST`, `REDIS_PORT`
- `FILESYSTEM_DISK`, `AWS_ENDPOINT`, `AWS_BUCKET`, `AWS_USE_PATH_STYLE_ENDPOINT`
- `METERPIPE_ADMIN_ALLOWED_EMAILS`
- `METERPIPE_ADMIN_DEV_LOGIN_ENABLED`
- `METERPIPE_ADMIN_DEV_LOGIN_EMAIL`
- `OPENAI_ADMIN_KEY`
- `OPENAI_COST_SYNC_DAYS`
- `LARAVEL_CLOUD_API_TOKEN`
- `LARAVEL_CLOUD_COST_SYNC_DAYS`
- `METERPIPE_DEFAULT_CURRENCY`
- `METERPIPE_COST_SYNC_QUEUE`
- `METERPIPE_COST_SYNC_POLLING_INTERVAL`
- `METERPIPE_MONTHLY_BUDGET_AMOUNT`
- `METERPIPE_MONTHLY_BUDGET_CURRENCY`

## Cost Sync

- `METERPIPE_OPENAI_COLLECTOR_ENABLED`: `true` の場合、OpenAI provider を通常同期対象にします。
- `OPENAI_ADMIN_KEY`: OpenAI Admin API key。DB や UI には保存しません。
- `OPENAI_COST_SYNC_DAYS`: OpenAI command の default 取得日数です。
- `METERPIPE_LARAVEL_CLOUD_COLLECTOR_ENABLED`: `true` の場合、Laravel Cloud provider を通常同期対象にします。
- `LARAVEL_CLOUD_API_TOKEN`: Laravel Cloud API token。DB や UI には保存しません。
- `LARAVEL_CLOUD_COST_SYNC_DAYS`: Laravel Cloud command の default 取得日数です。
- `METERPIPE_DEFAULT_CURRENCY`: Dashboard の default currency 表示です。
- `METERPIPE_COST_SYNC_QUEUE`: cost sync job を投入する queue 名です。
- `METERPIPE_COST_SYNC_POLLING_INTERVAL`: Filament widget の polling interval です。

## Secret Handling

`OPENAI_ADMIN_KEY`、`LARAVEL_CLOUD_API_TOKEN` などの secret はログ、DB、テスト出力に出しません。Cost Provider の `settings` は非secret設定だけに使います。後続で保存が必要になった場合は Laravel encrypted cast を使います。
