FROM php:8.1-apache

# 한 번에 모든 패키지 설치 및 정리
RUN apt-get update && apt-get install -y \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libzip-dev libonig-dev libxml2-dev libcurl4-openssl-dev \
    curl zip unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql mysqli zip mbstring xml curl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# 그누보드 파일 복사
COPY . /var/www/html/

# 권한 설정 및 정리
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/data_template/{cache,session,file,log,member,member_image} \
    # && chmod -R 707 /var/www/html/data \
    && rm -rf /var/www/html/install

EXPOSE 80
CMD ["apache2-foreground"]