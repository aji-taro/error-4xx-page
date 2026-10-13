<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $exception->getStatusCode() }} | 自作の 4xx.blade.php</title>
    <style>
        body { font-family: sans-serif; background: #fff8e1; color: #333; margin: 0; }
        main { max-width: 640px; margin: 80px auto; padding: 32px; background: #fff; border: 4px solid #ff9800; border-radius: 8px; }
        h1 { color: #e65100; margin-top: 0; }
        .code { font-size: 64px; font-weight: bold; color: #ff9800; }
    </style>
</head>
<body>
<main>
    <h1>自作の 4xx.blade.php が表示されています</h1>
    <div class="code">{{ $exception->getStatusCode() }}</div>
    <p>resources/views/errors/4xx.blade.php</p>
    <p><a href="/">トップページへ戻る</a></p>
</main>
</body>
</html>
