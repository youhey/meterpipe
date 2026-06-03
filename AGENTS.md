# AGENTS.md

この文書は、このリポジトリで作業する Agent 向けのプロジェクト固有ルールです。

## Project Overview

`meterpipe` は PipeKit 全体の cost、usage、collector 稼働状況、analytics event を確認するための Laravel + Filament ダッシュボードです。

Phase 1 では Laravel / Filament / Docker Compose / CI / Makefile / Laravel Cloud 対応の土台を実装しました。

Phase 2 では OpenAI Organization Costs API と Laravel Cloud Usage API の cost sync、DB 保存、Cost Dashboard、sync run 履歴、dimension mapping、budget 管理を扱います。

Dashboard 表示時に外部 API を直接呼ばないでください。外部 API 取得は Queue Job / Artisan Command に分離し、Filament widgets は保存済み DB data だけを参照します。

## Repository Layout

Laravel アプリ本体は必ず `src/` 配下に置きます。

```txt
docs/                 # Development documents
docker/               # Dockerfiles and container configuration
src/                  # Laravel application source
.github/              # GitHub Actions
docker-compose.yml    # Local development environment
Makefile
README.md
AGENTS.md
```

Laravel framework files をリポジトリルート直下に置かないでください。

## Target Runtime

本番の deployment target は Laravel Cloud です。

- Application containers are ephemeral.
- local filesystem を persistent storage として使いません。
- logs は stdout / stderr に出します。
- DB / cache / session / queue / filesystem は `.env` で切り替え可能にします。
- Laravel MySQL を標準の DB 前提にします。
- build-time と deploy-time の責務を分けます。

## Local Development Stack

Docker Compose を使います。

- `php-cli`: Composer, Artisan, PHPUnit, PHPStan, PHP-CS-Fixer
- `php-fpm`: Laravel web runtime
- `nginx`: local HTTP frontend
- `node`: npm and Vite
- `mysql`: database
- `valkey`: Redis-compatible cache/session
- `minio`: S3-compatible object storage

Web request path:

```txt
nginx -> php-fpm -> Laravel
```

PHP commands and tests should use `php-cli`.
Node.js, npm, and Vite commands should use `node`.

## Application Defaults

Local defaults:

```env
DB_CONNECTION=mysql
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=database
FILESYSTEM_DISK=s3
LOG_CHANNEL=stderr
LOG_STACK=stderr
```

SQLite、file cache、file session を project default にしないでください。

## Environment File Policy

実 secrets は commit しません。

Tracked:

```txt
.env.example
src/.env.example
```

Ignored:

```txt
.env
.env.*
src/.env
src/.env.*
```

OpenAI Admin Key、Laravel Cloud API token、Google OAuth secrets、S3 credentials、production environment values を commit しないでください。

Root `.env` は Docker Compose の port forwarding と middleware bootstrap values 用です。Laravel application settings は `src/.env.example` と `src/.env` に置きます。

## Laravel Cloud Compatibility

- production path で `storage/app` への永続保存を前提にしません。
- persistent storage は S3 compatible disk を使います。
- stderr logging を維持します。
- queue, session, cache, storage は `.env` で切り替えます。
- shell access や mutable container state に依存しません。
- custom nginx behavior を production assumption にしません。
- `php artisan storage:link` を deployment assumption にしません。

## Laravel Cloud Repository Detection

authoritative Composer project は `src/composer.json` / `src/composer.lock` です。

root `composer.lock` が必要になった場合でも、それは Laravel Cloud framework detection workaround です。root を Composer project root として扱わないでください。

dependencies を更新した場合は `src/` 側を正として扱い、必要なときだけ root lock を refresh します。

```bash
cp src/composer.lock composer.lock
```

## Build and Deploy Expectations

Laravel Cloud build-time tasks:

```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Deploy-time task:

```bash
php artisan migrate --force
```

## Testing

通常は Makefile を使います。

```bash
make test
make lint
```

`make test` は PHPUnit を実行します。`make lint` は PHPStan、PHP-CS-Fixer dry-run、Composer audit を実行します。

Automated tests must not call real external APIs. OpenAI Admin API、Laravel Cloud API、Google OAuth は fake、mock、HTTP fake、fixture を使って検証してください。

## Cost Sync Constraints

- provider の有効/無効は `config/meterpipe.php` で管理し、DB に provider 定義や secret を保存しません。
- `OPENAI_ADMIN_KEY` と `LARAVEL_CLOUD_API_TOKEN` は `.env` だけで管理します。
- sync は Queue Job / Artisan Command から実行します。
- widget の `getData()` / `getStats()` から外部 API を呼びません。
- `cost_records` は冪等に upsert します。
- dashboard 表示用には `cost_daily_summaries` を読みます。
- `cost_sync_runs.error_message` に secret を含めません。

## Code Style

既存の Laravel / Filament / sibling app の style を優先します。

- 最小変更を優先します。
- 既存 helper、config、service pattern を再利用します。
- 変更理由コメントは追加しません。
- 複雑な logic の説明が必要な場合だけ短いコメントを追加します。
- 変数名・関数名は英語にします。

## Security

- API key、OAuth token、admin key を管理画面や logs に表示しません。
- allow list が空の場合、admin panel には誰も入れません。
- local dev login helper は `local` / `testing` かつ明示有効化時だけ動かします。
- production で local dev login helper を有効にしません。
