# Architecture

meterpipe は collector job、保存 DB、Filament dashboard の 3 層で構成します。

```text
fake/manual collectors
  -> metric_snapshots / cost_daily_summaries / analytics_events / collector_runs
  -> CostSummaryService / CollectorHealthService
  -> Filament Dashboard / Resources
```

## Tables

- `pipe_apps`: PipeKit アプリ定義の source of truth
- `app_integrations`: 外部 provider と pipe app の mapping
- `metric_snapshots`: 汎用時系列 metric
- `cost_daily_summaries`: 日次コスト集計
- `analytics_events`: pipe アプリから受け取る成果・行動イベント
- `collector_runs`: collector 実行履歴

metric、cost、analytics、health を分ける理由は、取得元と粒度が異なるためです。Phase 1 では正規化しすぎず、後続 collector が自然に保存先を選べる構成にしています。
