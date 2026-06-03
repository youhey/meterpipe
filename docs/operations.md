# Operations

## Collector

```bash
make demo-seed
docker compose exec -T php-cli php artisan meterpipe:collect --all
docker compose exec -T php-cli php artisan meterpipe:collect --all --dry-run
```

失敗した collector は `/admin/collector-runs` で確認します。`error_message` には secret を含めない方針です。

## Cost Sync

手動で queue に投入する場合:

```bash
make sync-costs
```

同一 process で同期する場合:

```bash
docker compose exec -T php-cli php artisan meterpipe:sync-openai-costs --from=2026-06-01 --to=2026-06-02 --sync
docker compose exec -T php-cli php artisan meterpipe:sync-laravel-cloud-costs --from=2026-06-01 --to=2026-06-02 --sync
```

provider が disabled の場合は `skipped` になります。検証目的で disabled provider を実行する場合だけ `--force` を使います。

## Queue Worker

local で queue を処理する場合:

```bash
docker compose exec -T php-cli php artisan queue:work
```

Cost Dashboard の手動同期 action は外部 API の完了を待たず、Job を投入して終了します。進捗は `/admin/cost-dashboard` の Sync Status か `/admin/cost-sync-runs` で確認します。

## Recalculate

`cost_records` から `cost_daily_summaries` を再生成します。

```bash
docker compose exec -T php-cli php artisan meterpipe:recalculate-cost-summaries --from=2026-06-01 --to=2026-06-02
docker compose exec -T php-cli php artisan meterpipe:recalculate-cost-summaries --provider=openai
```

## Troubleshooting

- `queued` のまま: queue worker が動いているか確認します。
- `failed`: `/admin/cost-sync-runs` の `error_class` / `error_message` を確認します。secret は表示しない方針です。
- `skipped`: provider が disabled、または同じ provider + period の同期が実行中です。
- stale: `cost_sync_runs` の最新成功 run が 3 時間以上古い場合は warning、24 時間以上古い場合は danger として扱います。

## Data Refresh

local では以下で初期化できます。

```bash
docker compose exec -T php-cli php artisan migrate
docker compose exec -T php-cli php artisan db:seed
make demo-seed
```

完全な volume 削除は `make destroy` です。DB volume を削除するため、影響範囲は local Docker 環境全体です。

## Deployment Assumption

Laravel Cloud では `src/` を Composer project root として扱う想定です。root lockfile 検出が必要になった場合だけ `src/composer.lock` の root コピーを検討します。
