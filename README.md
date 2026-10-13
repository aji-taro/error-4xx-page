# error-4xx-page

Laravel 12 で `resources/views/errors/4xx.blade.php` を置いても、
**401 / 402 / 403 / 404 / 419 / 429 では 4xx.blade.php が表示されない** ことを再現するサンプルです。

## 動作環境

- Docker / Docker Compose
- Laravel 12.x(確認時: Laravel Framework 12.69.3)
- PHP 8.4(Docker イメージ `php:8.4-cli`)

ホストOSに PHP や composer は不要です。composer はコンテナ内で実行します。

## セットアップ

```sh
git clone <このリポジトリのURL> error-4xx-page
cd error-4xx-page

# イメージのビルド
docker compose build

# 依存パッケージのインストール
docker compose run --rm app composer install

# .env の作成とアプリケーションキーの生成
docker compose run --rm app cp .env.example .env
docker compose run --rm app php artisan key:generate

# SQLite データベースの作成とマイグレーション(セッションを DB に保存するため必要)
docker compose run --rm app sh -c "touch database/database.sqlite && php artisan migrate"

# 起動
docker compose up
```

docker compose up を停止するときは control + C で止めてください。


ブラウザで http://localhost:8000 を開くと、各ステータスコードへのリンク一覧が表示されます。


## 試し方

`routes/web.php` に、指定したステータスコードで `abort()` するルートを用意しています。

```php
Route::get('/debug_error/{code}', fn (int $code) => abort($code));
```

| URL | vendor 側の専用ページ | 表示されるページ |
|---|---|---|
| /debug_error/400 | なし | 自作の 4xx.blade.php |
| /debug_error/401 | あり | Laravel 標準の 401 ページ |
| /debug_error/402 | あり | Laravel 標準の 402 ページ |
| /debug_error/403 | あり | Laravel 標準の 403 ページ |
| /debug_error/404 | あり | Laravel 標準の 404 ページ |
| /debug_error/405 | なし | 自作の 4xx.blade.php |
| /debug_error/410 | なし | 自作の 4xx.blade.php |
| /debug_error/418 | なし | 自作の 4xx.blade.php |
| /debug_error/419 | あり | Laravel 標準の 419 ページ |
| /debug_error/422 | なし | 自作の 4xx.blade.php |
| /debug_error/429 | あり | Laravel 標準の 429 ページ |

## 原因

### 公式ドキュメントの記述

[Laravel 12.x のドキュメント (Fallback HTTP Error Pages)](https://laravel.com/docs/12.x/errors#fallback-http-error-pages) には、次のように書かれています。

> When defining fallback error pages, the fallback pages will not affect 404, 500, and 503 error responses since Laravel has internal, dedicated pages for these status codes.

ドキュメント上は 404 / 500 / 503 だけが対象外です。しかし実際には、フレームワーク内に次の専用ページがあります。

```
vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/views/
├── 401.blade.php
├── 402.blade.php
├── 403.blade.php
├── 404.blade.php
├── 419.blade.php
├── 429.blade.php
├── 500.blade.php
├── 503.blade.php
├── layout.blade.php
└── minimal.blade.php
```

### エラーページを探す処理

`errors::` 名前空間には、アプリ側の `resources/views/errors` と、上記の vendor 側のディレクトリが両方登録されています。

`vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/RegisterErrorViewPaths.php`

```php
View::replaceNamespace('errors', (new Collection(config('view.paths')))
    ->map(fn ($path) => "{$path}/errors")
    ->push(__DIR__.'/views')
    ->all()
);
```

エラーページは、まず `errors::{ステータスコード}` を探し、見つからなかったときだけ `errors::4xx` を探します。

`vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/Handler.php`

```php
protected function getHttpExceptionView(HttpExceptionInterface $e)
{
    $view = 'errors::'.$e->getStatusCode();

    if (view()->exists($view)) {
        return $view;
    }

    $view = substr($view, 0, -2).'xx';

    if (view()->exists($view)) {
        return $view;
    }

    return null;
}
```

そのため 403 などは、1 段目で vendor 側の `403.blade.php` が見つかり、`4xx.blade.php` まで処理が届きません。

## 対処法

401 / 402 / 403 / 404 / 419 / 429 も自作のページにしたい場合は、ステータスコードごとのファイルを `resources/views/errors` に置きます。
アプリ側のディレクトリが vendor 側より先に探されるため、こちらが優先されます。

中身を 4xx.blade.php と共通にしたいだけなら、次のように読み込むだけで済みます。

```blade
{{-- resources/views/errors/403.blade.php --}}
@include('errors.4xx')
```

vendor 側のページを元に作りたい場合は、次のコマンドで `resources/views/errors` にコピーできます。

```sh
docker compose run --rm app php artisan vendor:publish --tag=laravel-errors
```

## このサンプルで追加・変更したファイル

`composer create-project laravel/laravel:^12.0` で作ったプロジェクトに、次の変更を加えています。

- `Dockerfile` / `docker-compose.yml`: 追加
- `resources/views/errors/4xx.blade.php`: 追加
- `routes/web.php`: `/debug_error/{code}` ルートを追加
- `resources/views/welcome.blade.php`: 確認用リンク一覧に変更
- `.env.example`: `APP_URL` を `http://localhost:8000` に変更
- `README.md`: このファイル
