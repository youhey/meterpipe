# Admin

Filament admin panel は `/admin` です。

## Auth

管理画面の認証は Google OAuth で制御します。未認証の `/admin` は `/admin/login` へ進み、`/admin/login` が Google OAuth へ redirect します。`METERPIPE_ADMIN_ALLOWED_EMAILS` の allow list に含まれる Google account だけが panel access できます。

- public registration は有効化しません。
- password reset は有効化しません。
- allow list にない user は `/admin` へアクセスできません。

```dotenv
METERPIPE_ADMIN_ALLOWED_EMAILS=admin@example.test
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

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
