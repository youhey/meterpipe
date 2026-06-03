# meterpipe

Cost and Usage Metering Pipeline for PipeKit.

`meterpipe` is a small private Laravel + Filament dashboard for checking PipeKit-wide cost, usage, collector health, and analytics events.

Phase 1 provided the application foundation: Laravel, Filament, Docker Compose, CI, Makefile workflow, and Laravel Cloud compatibility.

Phase 2 adds OpenAI Organization Costs API and Laravel Cloud Usage API cost sync, normalized cost records, daily summaries, sync run history, dimension mapping, budget management, and a Filament Cost Dashboard that reads only saved DB data.

## Repository Structure

```txt
.
├── docs/
├── docker/
├── src/
├── .github/
├── .env.example
├── docker-compose.yml
├── Makefile
├── README.md
└── AGENTS.md
```

The Laravel application lives under `src/`. Do not place Laravel framework files in the repository root.

## Local Setup

```bash
make build
make up
```

Local defaults:

- Web: `http://localhost:8080`
- Admin panel: `http://localhost:8080/admin`
- Cost Dashboard: `http://localhost:8080/admin/cost-dashboard`
- MinIO console: `http://localhost:9001`
- MinIO credentials: `minioadmin` / `minioadmin`

The local stack uses MySQL, Valkey, and MinIO. Override forwarded ports in the root `.env` when running sibling apps at the same time.

Existing local `.env` files are not overwritten. If this repository was previously using `METERPIPE_*` forwarded port variables, refresh the root `.env` from `.env.example` or translate them to the sibling-standard `FORWARD_*` variables.

## Development Workflow

Use the Makefile as the normal entrypoint:

```bash
make build
make up
make test
make lint
make fix
make down
```

`make test` runs PHPUnit through the `php-cli` container. `make lint` runs PHPStan, PHP-CS-Fixer dry-run, and Composer audit. Node/Vite tasks run through the `node` service with `make front-build`.

Meterpipe-specific helpers:

```bash
make demo-seed
make sync-costs
make recalculate-cost-summaries
```

## Admin Panel

The Filament admin panel is available at `/admin`.

Required admin environment variables:

```env
METERPIPE_ADMIN_ALLOWED_EMAILS=admin@example.test
METERPIPE_ADMIN_DEV_LOGIN_ENABLED=false
METERPIPE_ADMIN_DEV_LOGIN_EMAIL=
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

`METERPIPE_ADMIN_ALLOWED_EMAILS` is comma-separated. Matching is case-insensitive and trims whitespace. If the allow list is empty, no user can access the admin panel.

Unauthenticated access to `/admin` reaches `/admin/login`, and `/admin/login` redirects to Google OAuth. The Google account email must be included in `METERPIPE_ADMIN_ALLOWED_EMAILS`.

For local browser debugging, `GET /_local/admin/login` logs in the configured development user only when `APP_ENV` is `local` or `testing`, `METERPIPE_ADMIN_DEV_LOGIN_ENABLED=true`, and the dev email is also allow-listed.

## Cost Sync

Dashboard widgets do not call external APIs. Cost data is synchronized by Queue Job or Artisan command, stored in DB, and then read from `cost_daily_summaries`.

```bash
docker compose exec -T php-cli php artisan meterpipe:sync-costs --provider=all --days=30
docker compose exec -T php-cli php artisan meterpipe:sync-openai-costs --from=2026-06-01 --to=2026-06-02 --sync
docker compose exec -T php-cli php artisan meterpipe:sync-laravel-cloud-costs --from=2026-06-01 --to=2026-06-02 --sync
docker compose exec -T php-cli php artisan meterpipe:recalculate-cost-summaries --from=2026-06-01 --to=2026-06-02
```

OpenAI and Laravel Cloud secrets live only in `src/.env`:

```env
METERPIPE_OPENAI_COLLECTOR_ENABLED=false
OPENAI_ADMIN_KEY=
METERPIPE_LARAVEL_CLOUD_COLLECTOR_ENABLED=false
LARAVEL_CLOUD_API_TOKEN=
```

Do not commit real API keys.

## Laravel Cloud

The production target is Laravel Cloud.

Important assumptions:

- Application containers are ephemeral.
- Persistent objects must use an S3-compatible disk, not local filesystem storage.
- Logs go to stdout/stderr.
- DB, cache, session, queue, and filesystem are selected by environment variables.
- Laravel MySQL is the expected database unless explicitly changed later.

The authoritative Composer project is `src/composer.json` and `src/composer.lock`.

If Laravel Cloud framework detection later requires a root-level `composer.lock`, copy it from `src/composer.lock` and treat it only as a detection workaround.

```bash
cp src/composer.lock composer.lock
```

## GitHub Workflow

GitHub Actions CI runs on pull requests and pushes to `main`. It validates Composer metadata, installs dependencies, audits Composer packages, runs MySQL migrations, PHPUnit, PHPStan, and PHP-CS-Fixer dry-run.

## Environment Files

Tracked examples:

- `.env.example`
- `src/.env.example`

Ignored local files:

- `.env`
- `.env.*`
- `src/.env`
- `src/.env.*`

Do not commit Google OAuth secrets, OpenAI Admin keys, Laravel Cloud API tokens, S3 credentials, or real production environment values.

## Docs

- [architecture](docs/architecture.md)
- [env](docs/env.md)
- [commands](docs/commands.md)
- [collectors](docs/collectors.md)
- [pipe apps](docs/pipe-apps.md)
- [costs](docs/costs.md)
- [metrics](docs/metrics.md)
- [analytics events](docs/analytics-events.md)
- [admin](docs/admin.md)
- [operations](docs/operations.md)
