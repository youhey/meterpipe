# Admin

Filament admin panel は `/admin` です。

## Auth

Phase 1 では Filament 標準 login を使い、`METERPIPE_ADMIN_ALLOWED_EMAILS` の allow list で panel access を制御します。

- public registration は有効化しません。
- password reset は有効化しません。
- allow list にない user は `/admin` へアクセスできません。

## Dev Login

local/testing だけ `/_local/admin/login` を使用できます。

```dotenv
METERPIPE_ADMIN_DEV_LOGIN_ENABLED=true
METERPIPE_ADMIN_DEV_LOGIN_EMAIL=admin@example.test
METERPIPE_ADMIN_ALLOWED_EMAILS=admin@example.test
```

production では env が true でも dev login は 404 になります。

## Resources

Editable:

- Pipe Apps
- App Integrations

Read-only:

- Metric Snapshots
- Cost Daily Summaries
- Analytics Events
- Collector Runs
