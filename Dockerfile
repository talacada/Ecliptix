# FrankenPHP with PHP 8.4
FROM dunglas/frankenphp:1.4-php8.4

# Install system utilities and dependencies
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

# Install required PHP extensions (pdo_pgsql for PostgreSQL)
RUN install-php-extensions \
    pdo_pgsql \
    intl \
    zip \
    opcache

# Allow Composer git clone regardless of directory owner
RUN git config --global --add safe.directory /app

# Install Composer
COPY --from=composer/composer:2-bin /composer /usr/bin/composer

WORKDIR /app

# Copy project files
COPY . /app/
RUN chown -R root:root /app

# Copy entrypoint script and make it executable
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Development environment
ENV APP_ENV=dev

ENTRYPOINT ["docker-entrypoint.sh"]
# Start FrankenPHP server
CMD ["frankenphp", "php-server", "--domain", "localhost", "-r", "public/"]
