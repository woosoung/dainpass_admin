# FROM php:8.1-apache

# # 환경 설정
# ENV DEBIAN_FRONTEND=noninteractive
# ENV TZ=Asia/Seoul

# # 기본 도구들 설치
# RUN apt-get update && apt-get install -y \
#     curl \
#     zip \
#     unzip \
#     wget \
#     git \
#     && apt-get clean \
#     && rm -rf /var/lib/apt/lists/*

# # PHP 확장을 위한 라이브러리 설치 (검증된 것만)
# RUN apt-get update && apt-get install -y \
#     libfreetype6-dev \
#     libjpeg62-turbo-dev \
#     libpng-dev \
#     libzip-dev \
#     libxml2-dev \
#     libcurl4-openssl-dev \
#     libpq-dev \
#     libonig-dev \
#     libicu-dev \
#     && apt-get clean \
#     && rm -rf /var/lib/apt/lists/*

# # GD 확장 설치
# RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
#     && docker-php-ext-install gd

# # 기본 확장들 설치
# RUN docker-php-ext-install zip
# RUN docker-php-ext-install mbstring
# RUN docker-php-ext-install xml
# RUN docker-php-ext-install curl

# # 데이터베이스 확장 설치
# RUN docker-php-ext-install pdo_mysql
# RUN docker-php-ext-install mysqli
# RUN docker-php-ext-install pgsql
# RUN docker-php-ext-install pdo_pgsql

# # 수학 관련 확장
# RUN docker-php-ext-install bcmath

# # 국제화 확장
# RUN docker-php-ext-configure intl && docker-php-ext-install intl

# # PECL 확장 설치
# RUN pecl install redis apcu \
#     && docker-php-ext-enable redis apcu \
#     && pecl clear-cache

# # Apache 모듈 활성화
# RUN a2enmod rewrite headers

# # Composer 설치
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# # 애플리케이션 파일 복사
# COPY . /var/www/html/

# # 기존 AWS SDK 제거
# RUN rm -rf /var/www/html/lib/aws

# # AWS SDK 설치 (Pod에서 성공한 방법 그대로)
# WORKDIR /var/www/html
# RUN composer require aws/aws-sdk-php
# RUN mv vendor lib/aws

# # 권한 설정
# RUN chown -R www-data:www-data /var/www/html \
#     && chmod -R 755 /var/www/html \
#     && rm -rf /var/www/html/install

# # Composer 파일 정리
# RUN rm -f composer.json composer.lock

# EXPOSE 80
# CMD ["apache2-foreground"]

# Base stage - 공통 설정
FROM php:8.1-apache AS base

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

# PHP 확장을 위한 라이브러리 설치
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libpq-dev \
    libonig-dev \
    libicu-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# GD 확장 설치
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# 기본 확장들 설치
RUN docker-php-ext-install zip mbstring xml curl pdo_mysql mysqli pgsql pdo_pgsql bcmath

# 국제화 확장
RUN docker-php-ext-configure intl && docker-php-ext-install intl

# PECL 확장 설치
RUN pecl install redis apcu \
    && docker-php-ext-enable redis apcu \
    && pecl clear-cache

# Apache 모듈 활성화
RUN a2enmod rewrite headers

# Composer 설치
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Development stage - 로컬 개발용
FROM base AS development

# 개발용 도구 설치
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Xdebug 설정
RUN echo "xdebug.mode=develop,debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# PHP 개발 설정
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/development.ini \
    && echo "display_errors = On" >> /usr/local/etc/php/conf.d/development.ini \
    && echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/development.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/development.ini

# 작업 디렉토리 설정 (볼륨 마운트용)
WORKDIR /var/www/html

# AWS SDK를 런타임에 설치하도록 스크립트 생성
RUN echo '#!/bin/bash\n\
if [ ! -d "/var/www/html/lib/aws" ]; then\n\
    echo "Installing AWS SDK..."\n\
    cd /var/www/html\n\
    if [ -f "composer.json" ]; then\n\
        composer install\n\
    else\n\
        composer require aws/aws-sdk-php\n\
    fi\n\
    mkdir -p lib\n\
    mv vendor lib/aws\n\
    chown -R www-data:www-data lib/aws\n\
    echo "AWS SDK installed successfully"\n\
fi\n\
exec apache2-foreground' > /usr/local/bin/dev-entrypoint.sh \
    && chmod +x /usr/local/bin/dev-entrypoint.sh

EXPOSE 80
CMD ["/usr/local/bin/dev-entrypoint.sh"]

# Production stage - EKS 서비스용 (기존 로직)
FROM base AS production

# 애플리케이션 파일 복사
COPY . /var/www/html/

# 기존 AWS SDK 제거
RUN rm -rf /var/www/html/lib/aws

# AWS SDK 설치 (기존 방법 그대로)
WORKDIR /var/www/html
RUN composer require aws/aws-sdk-php
RUN mv vendor lib/aws

# 권한 설정
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && rm -rf /var/www/html/install

# Composer 파일 정리
RUN rm -f composer.json composer.lock

# 프로덕션 PHP 설정
RUN echo "error_reporting = E_ERROR | E_WARNING | E_PARSE" >> /usr/local/etc/php/conf.d/production.ini \
    && echo "display_errors = Off" >> /usr/local/etc/php/conf.d/production.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/production.ini

EXPOSE 80
CMD ["apache2-foreground"]