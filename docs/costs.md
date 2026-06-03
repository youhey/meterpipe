# Costs

Phase 2 では OpenAI Organization Costs API と Laravel Cloud Usage API から cost data を取得し、DB に保存したデータだけを Filament Dashboard で表示します。

Dashboard の初期表示では外部 API を呼びません。手動 action、scheduler、または Artisan command が Queue Job を作成し、同期完了後に `cost_records` と `cost_daily_summaries` が更新されます。

## 取得対象

OpenAI:

- daily total cost
- `project_id` group
- `api_key_id` group
- `line_item` group

Laravel Cloud:

- organization spend
- application cost
- environment cost
- resource type cost
- add-on cost

Laravel Cloud Usage API はレスポンス構造が変わる可能性があるため、normalizer は取得できる項目だけを正規化し、対象部分を `raw_payload` に保存します。

## Schema

- `cost_sync_runs`: 同期履歴、status、期間、取得件数、失敗理由
- `cost_records`: 外部 API response を正規化した cost 明細
- `cost_daily_summaries`: Dashboard 用の日別集計
- `cost_dimension_mappings`: 外部 ID と表示名、PipeKit app の紐づけ
- `cost_budgets`: 月次 budget 表示用の設定

provider の定義と有効/無効は `config/meterpipe.php` の `cost_providers` に集約します。最終同期日時と stale 判定は `cost_sync_runs` の最新成功 run から計算します。

金額は `decimal(20, 8)` で保存します。`source_record_key` は provider、bucket 期間、dimension から作成し、同じ期間を再同期しても二重保存しません。

## Dashboard

`/admin/cost-dashboard` で以下を確認できます。

- 今月の総コスト
- 今月の OpenAI cost
- 今月の Laravel Cloud cost
- 昨日の総コスト
- 月末予測
- 最終同期日時
- 日別総コスト推移
- provider 別推移
- OpenAI project / line item 別 cost
- Laravel Cloud application / resource type 別 cost
- sync status

データがない場合は「まだコストデータが同期されていません」と表示します。

## App Mapping

`Cost Dimension Mappings` で外部 ID を PipeKit app に紐づけます。

例:

| provider_key | dimension_type | external_id | display_name | pipe_app_key |
|---|---|---|---|---|
| openai | project | proj_xxx | digestpipe | digestpipe |
| openai | api_key | key_xxx | radiopipe key | radiopipe |
| laravel_cloud | application | app_xxx | voicepipe | voicepipe |
| laravel_cloud | environment | env_xxx | production | voicepipe |

未設定の dimension は app 別集計では `Unmapped` 相当として扱われます。

## 月末予測

Phase 2 の月末予測は単純推定です。

```text
月末予測 = 今月累計 / 経過日数 * 当月日数
```

未取得日は除外しません。後続フェーズで直近 7 日平均や移動平均へ拡張できます。
