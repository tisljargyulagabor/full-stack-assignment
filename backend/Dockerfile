# PHP 8.5 FPM alapú image
FROM php:8.5-fpm

# Rendszerfüggőségek telepítése + build eszközök a PECL-hez
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    zip \
    unzip \
    libzip-dev \
    autoconf \
    gcc \
    make \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# PHP kiterjesztések telepítése (Postgres, BCMath a biztonsághoz, Zip)
RUN docker-php-ext-install pdo pdo_pgsql bcmath zip

# Redis kiterjesztés telepítése és engedélyezése
# Ez javítja ki az 500-as hibát a SESSION_DRIVER=redis esetén
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composer telepítése a legfrissebb hivatalos image-ből
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Munkakönyvtár beállítása
WORKDIR /var/www

# A teljes projekt másolása
COPY . .

# Jogosultságok beállítása (OWASP szempontból is fontos: nem root-ként futtatáshoz előkészítés)
# A Laravelnek írási jog kell a storage és cache mappákra
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# PHP függőségek telepítése (optimalizált autoloaderrel)
RUN composer install --no-interaction --optimize-autoloader

# A Laravel beépített szerverének indítása
# Megjegyzés: Élesben FPM+Nginx/Caddy ajánlott, de fejlesztéshez ez a leggyorsabb
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
