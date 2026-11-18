
FROM php:7.4-apache

WORKDIR /var/www/html

# Fix Debian Bullseye repositories
RUN set -eux; \
    find /etc/apt -type f \( -name '*.list' -o -name '*.sources' \) \
        -exec sed -i \
        -e 's|deb.debian.org/debian-security|archive.debian.org/debian-security|g' \
        -e 's|deb.debian.org/debian|archive.debian.org/debian|g' \
        {} +; \
    echo 'Acquire::Check-Valid-Until "false";' \
        > /etc/apt/apt.conf.d/99no-check-valid-until

# Install dependencies and PHP SQLite extension
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libsqlite3-dev \
        unzip \
        git \
    && docker-php-ext-install pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite
RUN a2enmod rewrite

# Set permissions
RUN chown -R www-data:www-data /var/www/html


