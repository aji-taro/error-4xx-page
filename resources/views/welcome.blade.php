@php
    // [ステータスコード => フレームワーク側に専用ページがあるか]
    $codes = [
        400 => false,
        401 => true,
        402 => true,
        403 => true,
        404 => true,
        405 => false,
        410 => false,
        418 => false,
        419 => true,
        422 => false,
        429 => true,
    ];
@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>4xx.blade.php 検証</title>
    <style>
        body { font-family: sans-serif; color: #333; margin: 0; }
        main { max-width: 720px; margin: 40px auto; padding: 0 16px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
        th { background: #f5f5f5; }
        .ok { color: #2e7d32; }
        .ng { color: #c62828; }
        code { background: #f5f5f5; padding: 2px 4px; }
    </style>
</head>
<body>
<main>
    <h1>4xx.blade.php が表示されないコードを確認する</h1>
    <p>
        Laravel {{ app()->version() }} /
        <code>resources/views/errors/4xx.blade.php</code> を配置済み
    </p>

    <table>
        <thead>
        <tr>
            <th>URL</th>
            <th>Laravel 側の専用ページ</th>
            <th>表示されるはずのページ</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($codes as $code => $hasFrameworkView)
            <tr>
                <td><a href="/debug_error/{{ $code }}">/debug_error/{{ $code }}</a></td>
                <td>{{ $hasFrameworkView ? "あり ({$code}.blade.php)" : 'なし' }}</td>
                <td class="{{ $hasFrameworkView ? 'ng' : 'ok' }}">
                    {{ $hasFrameworkView ? 'Laravel 標準のページ' : '自作の 4xx.blade.php' }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <p>
        vendor 側の専用ページの場所:<br>
        <code>vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/views/</code>
    </p>
</main>
</body>
</html>
