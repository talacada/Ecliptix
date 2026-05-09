# Volba FrankenPHP a PHP s verzí 8.4 (podmínky Composer.json)
FROM dunglas/frankenphp:1.4-php8.4

# Instalace základních utilit a systémových závislostí
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        apt-transport-https \
        ca-certificates \
        curl \
        git \
        gnupg \
        unzip \
        libzip-dev \
        libicu-dev \
        libpq-dev \
    && curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get install -y --no-install-recommends \
        symfony-cli \
    && rm -rf /var/lib/apt/lists/*

# Instalace potřebných PHP rozšíření (pro PostgreSQL je nutné pdo_pgsql)
RUN install-php-extensions \
    pdo_pgsql \
    intl \
    zip \
    opcache

# Nastavení výjimky pro bezpečný adresář, aby Composer git clone prošel bez ohledu na vlastníka
RUN git config --global --add safe.directory /app

# Instalace Composeru
COPY --from=composer/composer:2-bin /composer /usr/bin/composer

WORKDIR /app

# Zkopírování projektu
COPY . /app/
RUN chown -R root:root /app

# Přidání spouštěcího entrypointu a nastavení spustitelnosti
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Proměnná pro DEV mód
ENV APP_ENV=dev

ENTRYPOINT ["docker-entrypoint.sh"]
# Spuštění standardního skriptu/serveru FrankenPHP
CMD ["frankenphp", "php-server", "--domain", "localhost", "-r", "public/"]
