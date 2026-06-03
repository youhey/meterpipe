# Collectors

Collectors は、meterpipe に外部データや demo data を取り込むための小さな取得処理です。

`meterpipe:collect` は collector を実行し、collector が返したデータを `cost_daily_summaries`、`metric_snapshots`、`analytics_events` に保存します。実行履歴は `collector_runs` に保存します。

## Command

```bash
php artisan meterpipe:collect --collector=fake-openai-cost
php artisan meterpipe:collect --collector=fake-openai-usage
php artisan meterpipe:collect --collector=fake-laravel-cloud-cost
php artisan meterpipe:collect --collector=fake-pipe-analytics
php artisan meterpipe:collect --all
php artisan meterpipe:collect --all --dry-run
```

`--collector` は collector 名を指定します。

`--all` は登録済み fake collector をまとめて実行します。失敗確認用の `failing-test` は `--all` から除外します。

`--dry-run` は collector を実行しますが、DB には保存しません。`collector_runs` も作成しません。

## Execution Flow

通常実行の流れ:

1. `--collector` または `--all` から対象 collector を解決する
2. `collector_runs` に `running` の実行履歴を作成する
3. `CollectorContext` を作成して collector の `collect()` を実行する
4. `CollectorResult` の row を保存する
5. 成功時は `collector_runs.status=succeeded` に更新する
6. 失敗時は `collector_runs.status=failed` と `error_message` を保存する

`meterpipe:collect` は複数 collector を順番に実行します。途中の collector が失敗しても、対象 collector の走査は継続し、最終的な exit code は失敗になります。

## Interface

collector は `MetricCollector` を実装します。

```php
interface MetricCollector
{
    public function name(): string;

    public function collect(CollectorContext $context): CollectorResult;
}
```

`name()` は command の `--collector` で指定する安定名です。

`collect()` は取得処理を行い、保存対象を `CollectorResult` として返します。collector 自体は原則として DB 保存を担当しません。保存は `meterpipe:collect` がまとめて行います。

## CollectorContext

`CollectorContext` は collector 実行時の共通 context です。

| property | description |
|---|---|
| `dryRun` | dry-run 実行かどうか |
| `now` | 実行基準時刻 |

collector 内で現在時刻が必要な場合は `CarbonImmutable::now()` ではなく `context->now` を使います。

## CollectorResult

`CollectorResult` は collector の取得結果です。

| property | save target |
|---|---|
| `costDailySummaries` | `cost_daily_summaries` |
| `metricSnapshots` | `metric_snapshots` |
| `analyticsEvents` | `analytics_events` |
| `fetchedCount` | `collector_runs.fetched_count` |

`storedCount()` は `costDailySummaries`、`metricSnapshots`、`analyticsEvents` の件数合計です。成功時に `collector_runs.stored_count` へ保存します。

保存方法:

- `costDailySummaries`: `summary_key` を key に `updateOrCreate`
- `metricSnapshots`: `create`
- `analyticsEvents`: `create`

## Collector Runs

通常実行では、collector 開始時に `collector_runs.status=running` を作成します。

成功時:

- `status=succeeded`
- `finished_at` を保存
- `fetched_count` を保存
- `stored_count` を保存

失敗時:

- `status=failed`
- `finished_at` を保存
- `error_message` を保存

`error_message` は secret を含めない方針です。`openai_admin_key` と `laravel_cloud_api_token` に一致する値は `[redacted]` に置換します。

失敗した collector は `/admin/collector-runs` で確認します。

## Registered Collectors

現在 `meterpipe:collect` に登録されている collector は以下です。

| name | output |
|---|---|
| `fake-openai-cost` | OpenAI cost demo data を `cost_daily_summaries` に保存 |
| `fake-openai-usage` | OpenAI usage demo metric を `metric_snapshots` に保存 |
| `fake-laravel-cloud-cost` | Laravel Cloud cost demo data を `cost_daily_summaries` に保存 |
| `fake-pipe-analytics` | Pipe Apps ごとの metric と analytics event を保存 |
| `failing-test` | 失敗記録のテスト用 collector |

`failing-test` は明示的に `--collector=failing-test` と指定した場合だけ実行します。

## Fake Cost Collectors

`fake-openai-cost` と `fake-laravel-cloud-cost` は、dashboard 確認用の demo cost summary を作ります。

これらは `cost_daily_summaries` に直接保存する row を返します。Phase 2 の実 cost sync のように `cost_records` から再集計する経路ではありません。

本番の OpenAI / Laravel Cloud cost sync は `meterpipe:sync-costs` 系 command を使います。

## Fake Usage Collector

`fake-openai-usage` は OpenAI の usage metric を `metric_snapshots` に保存します。

例:

- `openai.requests`
- `openai.tokens.input`

metric naming と unit の方針は [metrics](metrics.md) を参照してください。

## Fake Analytics Collector

`fake-pipe-analytics` は `digestpipe` / `radiopipe` / `voicepipe` / `playpipe` を対象に、metric と analytics event を作ります。

保存対象:

- `metric_snapshots`: `app.requests`
- `analytics_events`: `demo.pipeline.completed`

Analytics Events のデータ仕様は [analytics-events](analytics-events.md) を参照してください。

## Cost Syncとの違い

`meterpipe:collect` は汎用 collector の実行口です。実行履歴は `collector_runs` に保存します。

OpenAI / Laravel Cloud の実 cost sync は、以下の専用 command と `cost_sync_runs` を使います。

```bash
php artisan meterpipe:sync-costs --provider=all --days=30
php artisan meterpipe:sync-costs --provider=openai --from=2026-06-01 --to=2026-06-02
php artisan meterpipe:sync-openai-costs --days=30
php artisan meterpipe:sync-laravel-cloud-costs --days=30
php artisan meterpipe:recalculate-cost-summaries --from=2026-06-01 --to=2026-06-02
```

`meterpipe:collect` と `meterpipe:sync-costs` は保存先と実行履歴が異なります。

| command | primary history table | primary data |
|---|---|---|
| `meterpipe:collect` | `collector_runs` | `metric_snapshots`, `analytics_events`, demo `cost_daily_summaries` |
| `meterpipe:sync-costs` | `cost_sync_runs` | `cost_records`, `cost_daily_summaries` |

## Scheduler

`routes/console.php` で hourly cost sync を登録しています。

```php
Schedule::command('meterpipe:sync-costs --days=7')
    ->hourly()
    ->withoutOverlapping();
```

現時点では `meterpipe:collect` の scheduler は登録していません。
