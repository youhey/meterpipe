# meterpipe

meterpipe は PipeKit 全体のコスト、使用量、collector 稼働状況、analytics event を確認するための Laravel + Filament ダッシュボードです。

Phase 1 では本番外部 API 連携を実装せず、今後 OpenAI Admin API、Laravel Cloud usage、各 pipe アプリの analytics ingestion を追加するための DB、collector、管理 UI の土台を提供します。

## 構成

- Laravel アプリ本体: `src/`
- 管理画面: `/admin`
- DB: MySQL
- Cache / Session: Valkey
- Queue: database
- ローカル入口: `Makefile`

## Local Setup

```bash
cp .env.example .env
cp src/.env.example src/.env
make build
make up
```

起動後、`http://localhost:8084/admin` にアクセスします。

local/testing では必要に応じて `src/.env` に以下を設定し、`/admin/dev-login` を使用できます。

```dotenv
METERPIPE_ADMIN_ALLOWED_EMAILS=admin@example.test
METERPIPE_ADMIN_DEV_LOGIN_ENABLED=true
METERPIPE_ADMIN_DEV_LOGIN_EMAIL=admin@example.test
```

本番環境では dev login は無効です。

## Development Workflow

```bash
make shell
make migrate
make seed
make demo-seed
make test
make lint
make fix
make front-build
```

主要 Artisan command:

```bash
php artisan meterpipe:collect --collector=fake-openai-cost
php artisan meterpipe:collect --collector=fake-openai-usage
php artisan meterpipe:collect --collector=fake-laravel-cloud-cost
php artisan meterpipe:collect --all
php artisan meterpipe:collect --all --dry-run
php artisan meterpipe:demo:seed
```

## Laravel Cloud

Composer project root は `src/` です。Laravel Cloud 側で root 検出が必要になった場合のみ、`src/composer.lock` を root にコピーする運用を検討します。

## Docs

- [architecture](docs/architecture.md)
- [env](docs/env.md)
- [collectors](docs/collectors.md)
- [metrics](docs/metrics.md)
- [admin](docs/admin.md)
- [operations](docs/operations.md)
