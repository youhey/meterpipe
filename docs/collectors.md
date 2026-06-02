# Collectors

Phase 1 の fake collector に加えて、Phase 2 では OpenAI / Laravel Cloud の cost sync collector を実装しています。

## Interface

```php
interface MetricCollector
{
    public function name(): string;

    public function collect(CollectorContext $context): CollectorResult;
}
```

## Commands

```bash
php artisan meterpipe:collect --collector=fake-openai-cost
php artisan meterpipe:collect --collector=fake-openai-usage
php artisan meterpipe:collect --collector=fake-laravel-cloud-cost
php artisan meterpipe:collect --all
php artisan meterpipe:collect --all --dry-run
```

通常実行では `collector_runs.status=running` を作成し、成功時は `succeeded`、失敗時は `failed` に更新します。`--dry-run` では DB 保存しません。

## Cost Sync Commands

```bash
php artisan meterpipe:sync-costs --provider=all --days=30
php artisan meterpipe:sync-costs --provider=openai --from=2026-06-01 --to=2026-06-02
php artisan meterpipe:sync-openai-costs --days=30
php artisan meterpipe:sync-laravel-cloud-costs --days=30
php artisan meterpipe:recalculate-cost-summaries --from=2026-06-01 --to=2026-06-02
```

`--sync` を付けると Queue Job を使わず同一 process で実行します。通常は command が `cost_sync_runs.status=queued` を作成し、Job が `running`、`succeeded`、`failed`、`skipped` に更新します。

`--force` は disabled provider を手動同期するための option です。

## OpenAI Cost Collector

OpenAI は Organization Costs API を使います。

- endpoint: `GET /v1/organization/costs`
- group: total, `project_id`, `api_key_id`, `line_item`
- pagination: `has_more` / `next_page`

保存時は `project_id`、`api_key_id`、`line_item` を `cost_records` に残し、必要に応じて `cost_dimension_mappings` で app に紐づけます。

## Laravel Cloud Cost Collector

Laravel Cloud は Usage API を使います。

- base URL: `https://cloud.laravel.com/api`
- endpoint: `GET /usage`
- auth: Bearer Token

レスポンス構造の変更に備え、normalizer は organization spend、applications、environments、resources、add-ons から取得できる金額だけを正規化します。

## Scheduler

`routes/console.php` で hourly sync を登録しています。

```php
Schedule::command('meterpipe:sync-costs --days=7')
    ->hourly()
    ->withoutOverlapping();
```

Queue worker が停止している場合、sync run は `queued` のまま残ります。

## App Analytics Ingestion Plan

digestpipe / radiopipe / voicepipe / playpipe から以下のような event を送る想定です。

- `pipeline.started`
- `pipeline.completed`
- `pipeline.failed`
- `content.generated`
- `delivery.completed`

prompt 全文、生成結果全文、生の個人 ID は保存しません。
