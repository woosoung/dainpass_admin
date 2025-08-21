FROM php:8.1-apache

# 필요한 패키지 설치
RUN apt-get update && apt-get install -y \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libzip-dev libonig-dev libxml2-dev curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql mysqli zip mbstring xml curl fileinfo

# Apache 모듈 활성화
RUN a2enmod rewrite headers

# 그누보드 파일들 복사
COPY . /var/www/html/

# 권한 설정
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80