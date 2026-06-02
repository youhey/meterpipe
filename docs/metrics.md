# Metrics

## Naming

metric name は `domain.metric` の形式を基本にします。

例:

- `openai.requests`
- `openai.tokens.input`
- `app.requests`
- `pipeline.completed`

## Units

- `usd`
- `request`
- `token`
- `second`
- `byte`
- `count`
- `percent`

## Dimensions

`dimensions` は JSON で保存します。検索や unique 判定が必要な場合は dedicated column または `dimensions_hash` を追加します。

`cost_daily_summaries` は `source + pipe_app_id + service + date + dimensions_hash` を同一集計単位として扱います。

## App Mapping

外部 provider の project/resource/api key ID と PipeKit app の対応は `app_integrations` で管理します。secret は保存しません。
