FROM php:8.1-apache

# 환경 설정
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Seoul

# 기본 도구들 설치
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    wget \
    git \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP 확장을 위한 라이브러리 설치 (검증된 것만)
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libpq-dev \
    libonig-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# GD 확장 설치 (가장 먼저)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# 기본 확장들 설치 (확실히 작동하는 것들)
RUN docker-php-ext-install zip
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install xml
RUN docker-php-ext-install curl

# 데이터베이스 확장 설치
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pgsql
RUN docker-php-ext-install pdo_pgsql

# 수학 관련 확장 (bcmath는 기본 포함)
RUN docker-php-ext-install bcmath

# PECL 확장 설치 (간단한 것만)
RUN pecl install redis apcu \
    && docker-php-ext-enable redis apcu \
    && pecl clear-cache

# Apache 모듈 활성화
RUN a2enmod rewrite headers

# Composer 설치
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# AWS SDK 설치
WORKDIR /var/www/html
RUN mkdir -p lib \
    && cd lib \
    && composer init --no-interaction --require="aws/aws-sdk-php:^3.0" \
    && composer install --no-dev --optimize-autoloader --no-interaction \
    && mv vendor/aws lib/aws \
    && rm -rf vendor composer.json composer.lock \
    && composer clear-cache

# 애플리케이션 파일 복사
COPY . /var/www/html/

# 권한 설정 및 정리
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && rm -rf /var/www/html/install

EXPOSE 80
CMD ["apache2-foreground"]