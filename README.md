# meterpipe

meterpipe は PipeKit 全体のコスト、使用量、collector 稼働状況、analytics event を確認するための Laravel + Filament ダッシュボードです。

Phase 2 では OpenAI Organization Costs API と Laravel Cloud Usage API の cost sync、保存済みDBデータを読む Filament Cost Dashboard、手動同期 action、sync run 履歴、dimension mapping、budget 管理を提供します。

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
php artisan meterpipe:sync-costs --provider=all --days=30
php artisan meterpipe:sync-openai-costs --from=2026-06-01 --to=2026-06-02 --sync
php artisan meterpipe:sync-laravel-cloud-costs --from=2026-06-01 --to=2026-06-02 --sync
php artisan meterpipe:recalculate-cost-summaries --from=2026-06-01 --to=2026-06-02
```

## Laravel Cloud

Composer project root は `src/` です。Laravel Cloud 側で root 検出が必要になった場合のみ、`src/composer.lock` を root にコピーする運用を検討します。

## Docs

- [architecture](docs/architecture.md)
- [env](docs/env.md)
- [collectors](docs/collectors.md)
- [costs](docs/costs.md)
- [metrics](docs/metrics.md)
- [admin](docs/admin.md)
- [operations](docs/operations.md)
