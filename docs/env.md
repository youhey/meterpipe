# Environment

root `.env` は Docker Compose 用、`src/.env` は Laravel 用です。

## root `.env`

- `METERPIPE_WEB_PORT`: nginx 公開 port
- `METERPIPE_VITE_PORT`: Vite 公開 port
- `METERPIPE_DB_PORT`: MySQL 公開 port
- `METERPIPE_REDIS_PORT`: Valkey 公開 port
- `MYSQL_DATABASE`
- `MYSQL_USER`
- `MYSQL_PASSWORD`
- `MYSQL_ROOT_PASSWORD`

## `src/.env`

- `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION`
- `REDIS_HOST`, `REDIS_PORT`
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
