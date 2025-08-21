FROM php:8.1-apache

# 시스템 업데이트
RUN apt-get update

# 기본 필수 패키지 설치
RUN apt-get install -y \
    curl \
    zip \
    unzip

# 이미지 처리 관련 라이브러리
RUN apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev

# 기타 필요 라이브러리
RUN apt-get install -y \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev

# GD 확장 설정 및 설치
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# 데이터베이스 관련 확장
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install mysqli

# 기타 PHP 확장들
RUN docker-php-ext-install zip
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install xml
RUN docker-php-ext-install curl

# 캐시 정리
RUN rm -rf /var/lib/apt/lists/*

# Apache 모듈 활성화
RUN a2enmod rewrite
RUN a2enmod headers

# Apache 설정
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# 그누보드 파일들 복사
COPY . /var/www/html/

# 권한 설정
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# data 디렉토리 생성 및 권한.
RUN mkdir -p /var/www/html/data/{cache,session,file,log,member,member_image}
RUN chmod -R 707 /var/www/html/data

# install 디렉토리 제거
RUN rm -rf /var/www/html/install

EXPOSE 80
CMD ["apache2-foreground"]