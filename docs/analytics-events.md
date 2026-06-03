# Analytics Events

Analytics Events は、PipeKit 各アプリから受け取る成果・行動イベントを保存し、Filament 管理画面で確認するための機能です。

metric や cost は数値の時系列データですが、Analytics Events は「何が起きたか」を表すイベント台帳として扱います。例として、pipeline の完了、配信完了、コンテンツ生成完了などを記録します。

## Data Model

Analytics Events は `analytics_events` テーブルに保存します。

| column | description |
|---|---|
| `pipe_app_id` | 対象アプリ。`pipe_apps` への FK |
| `event_name` | イベント名。例: `pipeline.completed` |
| `subject_type` | イベント対象の種別 |
| `subject_id` | イベント対象の ID |
| `actor_type` | 実行主体の種別 |
| `actor_id_hash` | 実行主体 ID の hash |
| `properties` | 任意の JSON metadata |
| `occurred_at` | 実際にイベントが発生した日時 |
| `created_at`, `updated_at` | meterpipe 側で保存・更新した日時 |

`properties` は JSON で保存します。検索や集計で頻繁に使う値が出てきた場合は、後続フェーズで dedicated column への切り出しを検討します。

## Event Naming

`event_name` は `domain.action` の形式を基本にします。

例:

- `pipeline.started`
- `pipeline.completed`
- `pipeline.failed`
- `content.generated`
- `delivery.completed`

demo data では `demo.pipeline.completed` を使っています。

## Ingestion

collector は `CollectorResult::$analyticsEvents` にイベント行を入れて返します。

`php artisan meterpipe:collect` は collector 実行後、dry-run でない場合に `AnalyticsEvent::create()` で各イベントを保存します。

```php
return new CollectorResult(
    fetchedCount: count($events),
    analyticsEvents: $events,
);
```

現状の fake collector は `digestpipe` / `radiopipe` / `voicepipe` / `playpipe` 向けに demo event を生成します。`php artisan meterpipe:demo:seed` でも管理画面確認用の demo event を投入します。

## Admin UI

Filament の `Analytics Events` resource で確認します。

一覧で確認できる項目:

- `occurred_at`
- app key
- `event_name`
- `subject_type`
- `subject_id`
- `actor_type`

詳細画面では `actor_id_hash` と `properties` も確認できます。現状は閲覧中心の resource として扱います。

## Privacy

Analytics Events には以下を保存しません。

- prompt 全文
- 生成結果全文
- 生の個人 ID
- API key、OAuth token、secret

実行主体を記録する必要がある場合は、`actor_id_hash` に hash 化した ID を保存します。`properties` にも secret や復元可能な個人情報を入れないでください。

## Metricsとの違い

`metric_snapshots` は数値を時系列で見るための保存先です。例: request count、token count、処理時間。

`analytics_events` は個別イベントを後から追うための保存先です。例: pipeline がいつ、どの app で、どの subject に対して完了したか。

同じ事象から metric と event の両方を作ることはあります。例えば pipeline 完了時に、`analytics_events` へ `pipeline.completed` を保存し、`metric_snapshots` へ `pipeline.completed=count:1` を保存する運用は許容します。
