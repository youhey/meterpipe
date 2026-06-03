# Costs

Phase 2 では OpenAI Organization Costs API と Laravel Cloud Usage API から cost data を取得し、DB に保存したデータだけを Filament Dashboard で表示します。

Dashboard の初期表示では外部 API を呼びません。手動 action、scheduler、または Artisan command が Queue Job を作成し、同期完了後に `cost_records` と `cost_daily_summaries` が更新されます。

## 取得対象

OpenAI:

- daily total cost
- `project_id` group
- `line_item` group

OpenAI cost sync は、`App Integrations` に登録された有効な `provider=openai` / `provider_project_id` を取得対象 Project として使います。登録がない場合は Organization 全体の cost を取得せず、sync run を `skipped` にします。

Laravel Cloud:

- organization spend
- application cost
- environment cost
- resource type cost
- add-on cost

Laravel Cloud Usage API は `GET /api/usage` を使い、`period` query で billing period を指定します。meterpipe は `period=0`, `period=1`, `period=2` を同期対象にし、指定された `from` / `to` と重なる billing period をそれぞれ取得します。API response の金額は `current_spend_cents`、`total_cost_cents`、`total_cents` など cents 単位の値として返るため、normalizer で decimal currency amount に変換します。

Laravel Cloud Usage API は billing period ベースです。保存時の `bucket_date` / `bucket_end` はレスポンスの `meta.period` と `meta.available_periods` にある対象 billing period の `from` / `to` から決定します。`period=0..2` に含まれない期間の請求は同期対象外です。

## Schema

- `cost_sync_runs`: 同期履歴、status、期間、取得件数、失敗理由
- `cost_records`: 外部 API response を正規化した cost 明細
- `cost_daily_summaries`: Dashboard 用の日別集計
- `cost_dimension_mappings`: 外部 ID と表示名、PipeKit app の紐づけ
- `cost_budgets`: 月次 budget 表示用の設定

provider の定義と有効/無効は `config/meterpipe.php` の `cost_providers` に集約します。最終同期日時と stale 判定は `cost_sync_runs` の最新成功 run から計算します。

金額は `decimal(20, 8)` で保存します。`source_record_key` は provider、bucket 期間、dimension から作成し、同じ期間を再同期しても二重保存しません。

`cost_records.source_dimension_type` は、外部 API から取得した行の粒度を表します。Dashboard の総額や provider 別総額は `total` 粒度だけを合算し、`project` や `line_item` の内訳行を総額に混ぜません。

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

OpenAI の同期対象 Project は `App Integrations` で管理します。`provider=openai`、`enabled=true`、`provider_project_id` が設定された行だけが OpenAI Costs API の Project filter に使われます。

同じ OpenAI Project が複数の Pipe App に紐づく場合、その Project の cost は同期対象にはなりますが、特定の Pipe App へ自動配賦しません。このケースでは OpenAI Costs API の Project 粒度だけでは digestpipe と radiopipe のような複数アプリ間で cost を分割できないためです。

`Cost Dimension Mappings` は、外部 ID の表示名や Laravel Cloud などの dimension を PipeKit app に紐づける補助的な mapping として使います。

例:

| provider_key | dimension_type | external_id | display_name | pipe_app_key |
|---|---|---|---|---|
| openai | project | proj_xxx | digestpipe | digestpipe |
| laravel_cloud | application | app_xxx | voicepipe | voicepipe |
| laravel_cloud | environment | env_xxx | production | voicepipe |

未設定の dimension は app 別集計では `Unmapped` 相当として扱われます。

## 月末予測

Phase 2 の月末予測は単純推定です。

```text
月末予測 = 今月累計 / 経過日数 * 当月日数
```

未取得日は除外しません。後続フェーズで直近 7 日平均や移動平均へ拡張できます。
