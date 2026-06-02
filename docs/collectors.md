# Collectors

Phase 1 の collector は fake / placeholder のみです。本番外部 API へは接続しません。

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

## OpenAI Collector Plan

後続 Phase で OpenAI Admin API の以下を確認して実装します。

- `/organization/costs`
- `/organization/usage/completions`
- `/organization/usage/embeddings`
- `/organization/usage/audio_speeches`
- `/organization/usage/audio_transcriptions`
- `/organization/usage/images`
- `/organization/usage/vector_stores`

`project_id`、`api_key_id`、`model`、`line_item` などで group できます。`user_id` は個人情報混入リスクがあるため、そのまま保存しません。

## Laravel Cloud Collector Plan

TODO: confirm official API or export method.

Laravel Cloud Usage と同等の情報を外部 API で取得できるかは未確認です。API で取得できない場合は、手動入力、CSV import、または app 側独自計測で補完します。

## App Analytics Ingestion Plan

digestpipe / radiopipe / voicepipe / playpipe から以下のような event を送る想定です。

- `pipeline.started`
- `pipeline.completed`
- `pipeline.failed`
- `content.generated`
- `delivery.completed`

prompt 全文、生成結果全文、生の個人 ID は保存しません。
