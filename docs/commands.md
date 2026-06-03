# Commands

meterpipe の運用で使う command は、cost sync、collector、集計再計算、demo data、Laravel 標準運用に分かれます。

## Cost Sync

Cost Sync は OpenAI / Laravel Cloud の cost data を同期し、Cost Dashboard 用のデータを作る運用 command です。

実行履歴は `cost_sync_runs` に保存します。同期した明細は `cost_records` に保存し、dashboard 用の日別集計は `cost_daily_summaries` に保存します。

### `meterpipe:sync-costs`

OpenAI / Laravel Cloud の cost sync をまとめて実行します。通常運用の中心になる command です。

```bash
php artisan meterpipe:sync-costs --provider=all --days=30
php artisan meterpipe:sync-costs --provider=openai --from=2026-06-01 --to=2026-06-02
php artisan meterpipe:sync-costs --provider=laravel_cloud --days=7
```

主な options:

| option | description |
|---|---|
| `--provider=all` | `openai`, `laravel_cloud`, `all` のいずれか |
| `--from=` | 同期開始日。`YYYY-MM-DD` |
| `--to=` | 同期終了日。`YYYY-MM-DD` |
| `--days=30` | `from/to` 未指定時の取得日数 |
| `--sync` | Queue Job を使わず同一 process で実行 |
| `--force` | disabled provider も実行 |

`routes/console.php` では `Asia/Tokyo` の 08:30 / 18:00 に daily sync として登録しています。

```php
$command = 'meterpipe:sync-costs --days=7';

foreach (['08:30', '18:00'] as $time) {
    Schedule::command($command)
        ->dailyAt($time)
        ->timezone('Asia/Tokyo')
        ->name("meterpipe:sync-costs:{$time}")
        ->withoutOverlapping(30);
}
```

### `meterpipe:sync-openai-costs`

OpenAI cost だけを同期します。OpenAI 側だけ再同期したい場合や、provider 切り分けに使います。

```bash
php artisan meterpipe:sync-openai-costs --days=30
php artisan meterpipe:sync-openai-costs --from=2026-06-01 --to=2026-06-02 --sync
```

`--days` 未指定時は `OPENAI_COST_SYNC_DAYS` を使います。

### `meterpipe:sync-laravel-cloud-costs`

Laravel Cloud cost だけを同期します。Laravel Cloud 側だけ再同期したい場合や、provider 切り分けに使います。

```bash
php artisan meterpipe:sync-laravel-cloud-costs --days=30
php artisan meterpipe:sync-laravel-cloud-costs --from=2026-06-01 --to=2026-06-02 --sync
```

`--days` 未指定時は `LARAVEL_CLOUD_COST_SYNC_DAYS` を使います。

Laravel Cloud Usage API は billing period ベースのため、`--from` / `--to` と重なる `period=0`, `period=1`, `period=2` をそれぞれ取得します。保存時の日付は API response の `meta.period` / `meta.available_periods` に従います。

## Recalculate

### `meterpipe:recalculate-cost-summaries`

保存済みの `cost_records` から `cost_daily_summaries` を再生成します。外部 API は呼びません。

```bash
php artisan meterpipe:recalculate-cost-summaries --from=2026-06-01 --to=2026-06-02
php artisan meterpipe:recalculate-cost-summaries --provider=openai
php artisan meterpipe:recalculate-cost-summaries --provider=laravel_cloud
```

主な用途:

- cost dimension mapping を変更した後の再集計
- sync 済み明細から dashboard 集計を作り直す
- dashboard 表示の補正

`--provider` は `openai`, `laravel_cloud`, `all` のいずれかです。

## Collectors

### `meterpipe:collect`

汎用 collector を実行します。collector が返したデータを `metric_snapshots`、`analytics_events`、demo `cost_daily_summaries` に保存します。

実行履歴は `collector_runs` に保存します。

```bash
php artisan meterpipe:collect --collector=fake-openai-cost
php artisan meterpipe:collect --collector=fake-openai-usage
php artisan meterpipe:collect --collector=fake-laravel-cloud-cost
php artisan meterpipe:collect --collector=fake-pipe-analytics
php artisan meterpipe:collect --all
php artisan meterpipe:collect --all --dry-run
```

主な options:

| option | description |
|---|---|
| `--collector=` | 実行する collector 名 |
| `--all` | 登録済み fake collector をまとめて実行 |
| `--dry-run` | DB 保存しない。`collector_runs` も作成しない |

Collector の詳細は [collectors](collectors.md) を参照してください。

## Demo Data

### `meterpipe:demo:seed`

local / demo 確認用の dashboard data を投入します。

```bash
php artisan meterpipe:demo:seed
```

投入対象:

- Pipe Apps seed
- demo cost summaries
- demo metric snapshots
- demo analytics events
- demo collector runs

production では `--force` がないと実行できません。

```bash
php artisan meterpipe:demo:seed --force
```

## Laravel Standard Commands

### `migrate --force`

deploy 時の DB migration に使います。

```bash
php artisan migrate --force
```

### `queue:work`

Cost sync job など Queue Job を処理します。

```bash
php artisan queue:work
```

Cost Dashboard の手動 sync action や `meterpipe:sync-costs` の通常実行は Queue Job を投入するため、queue worker が必要です。

### `schedule:run`

Laravel scheduler を実行します。

```bash
php artisan schedule:run
```

meterpipe では `meterpipe:sync-costs --days=7` が `Asia/Tokyo` の 08:30 / 18:00 に登録されています。

## Makefile Wrappers

local Docker 環境では Makefile 経由で実行できます。

```bash
make sync-costs
make recalculate-cost-summaries
make demo-seed
```

対応する command:

| make target | command |
|---|---|
| `make sync-costs` | `php artisan meterpipe:sync-costs --provider=all --days=30` |
| `make recalculate-cost-summaries` | `php artisan meterpipe:recalculate-cost-summaries` |
| `make demo-seed` | `php artisan meterpipe:demo:seed` |

## Command Roles

| command | primary history table | primary data |
|---|---|---|
| `meterpipe:sync-costs` | `cost_sync_runs` | `cost_records`, `cost_daily_summaries` |
| `meterpipe:sync-openai-costs` | `cost_sync_runs` | `cost_records`, `cost_daily_summaries` |
| `meterpipe:sync-laravel-cloud-costs` | `cost_sync_runs` | `cost_records`, `cost_daily_summaries` |
| `meterpipe:recalculate-cost-summaries` | none | `cost_daily_summaries` |
| `meterpipe:collect` | `collector_runs` | `metric_snapshots`, `analytics_events`, demo `cost_daily_summaries` |
| `meterpipe:demo:seed` | demo `collector_runs` | demo dashboard data |
