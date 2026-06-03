# Pipe Apps

Pipe Apps は、meterpipe が扱う PipeKit アプリの基本台帳です。

`digestpipe`、`radiopipe`、`voicepipe`、`playpipe`、`meterpipe` のようなアプリ単位を `pipe_apps` に登録し、Metrics、Analytics Events、Cost、App Integrations の app 軸として利用します。

## Data Model

Pipe Apps は `pipe_apps` テーブルに保存します。

| column | description |
|---|---|
| `key` | アプリの安定識別子。例: `digestpipe` |
| `name` | 表示名 |
| `description` | 説明 |
| `repository_url` | リポジトリ URL |
| `base_url` | アプリの URL |
| `status` | `active`, `inactive`, `planned` |
| `metadata` | 任意の JSON metadata |
| `created_at`, `updated_at` | DB 保存・更新日時 |

`key` は他のテーブルから参照される app identifier として扱うため、後から変更しない前提です。

## Initial Apps

`PipeAppSeeder` は以下の初期データを投入します。

| key | status |
|---|---|
| `digestpipe` | `active` |
| `radiopipe` | `active` |
| `voicepipe` | `planned` |
| `playpipe` | `planned` |
| `meterpipe` | `active` |

Seeder は `key` を natural key として `updateOrCreate` します。

## Admin UI

Filament の `Pipe Apps` resource で作成・編集・削除できます。

一覧で確認できる項目:

- `key`
- `name`
- `status`
- `base_url`
- `updated_at`

`status` は filter できます。

## App Integrations

App Integrations は、Pipe App と外部 provider 側の識別子を対応づける管理データです。

`app_integrations.pipe_app_id` が `pipe_apps.id` を参照します。1つの Pipe App は複数の integration を持てます。

| column | description |
|---|---|
| `pipe_app_id` | 対象 Pipe App |
| `provider` | 外部 provider。例: `openai`, `laravel_cloud`, `manual` |
| `provider_project_id` | provider 側 project ID |
| `provider_api_key_id` | provider 側 API key ID |
| `provider_resource_id` | provider 側 resource ID |
| `label` | 人間向けの表示名 |
| `metadata` | 任意の JSON metadata |
| `enabled` | 有効/無効 |

想定用途は、OpenAI の project、Laravel Cloud の application / resource など、外部 provider の ID がどの Pipe App に対応するかを管理することです。

OpenAI cost sync では、有効な `provider=openai` の `provider_project_id` を OpenAI Costs API の Project filter として使います。これにより、Organization 全体ではなく、PipeKit で管理する Project だけを同期対象にします。

同じ OpenAI Project を複数の Pipe App が共有している場合、その Project は同期対象になりますが、meterpipe は特定の Pipe App へ cost を自動配賦しません。Project 粒度だけではアプリ別の利用額を確定できないためです。

App Integrations には API key や OAuth token などの secret を保存しません。外部 ID、表示名、管理用 metadata だけを保存します。

## Related Data

Pipe Apps は以下のデータと関係します。

- `app_integrations.pipe_app_id`: 外部 provider ID と Pipe App の対応
- `metric_snapshots.pipe_app_id`: metric を Pipe App に紐づける
- `analytics_events.pipe_app_id`: event を Pipe App に紐づける
- `cost_records.pipe_app_key`: cost record を Pipe App key に紐づける
- `cost_daily_summaries.pipe_app_key`: cost dashboard の app 別集計
- `cost_dimension_mappings.pipe_app_key`: provider dimension を Pipe App key に紐づける
- `cost_budgets.pipe_app_key`: Pipe App 単位の budget

## Cost Mappingとの関係

OpenAI cost sync では、App Integrations を同期対象 Project の source of truth として使います。

App Integrations は汎用的な外部 provider と Pipe App の対応表です。一方、`cost_dimension_mappings` は cost provider の dimension、例えば OpenAI project や Laravel Cloud application/resource を cost 集計に使うための対応表です。

どちらも Pipe App への mapping ですが、用途が分かれています。

- App Integrations: 外部 provider ID と Pipe App の汎用対応表、OpenAI 同期対象 Project の定義
- Cost Dimension Mappings: cost sync / cost dashboard で使う dimension 対応表

同一 Project が複数 Pipe App に紐づく場合は app 別集計へは使わず、Project / line item の dimension 集計として扱います。
