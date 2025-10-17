FROM php:8.3-fpm

# -----------------------------------------------------
# 1. System dependencies
# -----------------------------------------------------
RUN apt-get update && apt-get install -y \
    git curl zip unzip supervisor libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev libicu-dev libbz2-dev libcurl4-openssl-dev \
    libmemcached-dev libssl-dev pkg-config libpq-dev libxslt-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# -----------------------------------------------------
# 2. PHP extensions
# -----------------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    bcmath \
    bz2 \
    curl \
    exif \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_mysql \
    soap \
    xml \
    xsl \
    zip

# -----------------------------------------------------
# 3. Install and enable PECL extensions
# -----------------------------------------------------
RUN pecl install redis memcached \
    && docker-php-ext-enable redis memcached

# -----------------------------------------------------
# 4. Install Composer
# -----------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -----------------------------------------------------
# 5. Supervisor setup (for queue & octane)
# -----------------------------------------------------
COPY ./docker/supervisor /etc/supervisor/conf.d

# -----------------------------------------------------
# 6. Set working directory
# -----------------------------------------------------
WORKDIR /var/www

# -----------------------------------------------------
# 7. Optimize PHP-FPM configuration (optional but recommended)
# -----------------------------------------------------
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# -----------------------------------------------------
# 8. Start command
# -----------------------------------------------------
CMD ["php-fpm"]
