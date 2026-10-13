FROM php:8.4-cli

# composer が依存パッケージを取得するために git / unzip を入れる
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

# composer 本体は公式イメージからコピー（ホストOSに composer は不要）
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
