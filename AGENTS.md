# meterpipe Local Instructions

- Laravel アプリ本体は `src/` 配下を正とします。
- root の `Makefile` をローカル開発の入口にします。
- Composer / Artisan / npm は原則 `src/` を project root として実行します。
- collector は Phase 1 では fake / placeholder のみです。本番外部 API 連携を追加しないでください。
- API key / token / admin key をログ、DB、テスト出力に出さないでください。
