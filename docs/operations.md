# Operations

## Collector

```bash
make demo-seed
make shell
php artisan meterpipe:collect --all
php artisan meterpipe:collect --all --dry-run
```

失敗した collector は `/admin/collector-runs` で確認します。`error_message` には secret を含めない方針です。

## Data Refresh

local では以下で初期化できます。

```bash
make migrate
make seed
make demo-seed
```

完全な volume 削除は `make destroy` です。DB volume を削除するため、影響範囲は local Docker 環境全体です。

## Deployment Assumption

Laravel Cloud では `src/` を Composer project root として扱う想定です。root lockfile 検出が必要になった場合だけ `src/composer.lock` の root コピーを検討します。
