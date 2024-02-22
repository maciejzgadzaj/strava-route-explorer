FROM php:8.2-fpm-alpine

WORKDIR /application

RUN apk update \
    && apk add --no-cache --upgrade --virtual .phpize-deps $PHPIZE_DEPS \
        autoconf \
        curl \
        freetype-dev \
        git \
        g++ \
        icu-dev \
        libjpeg-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        libssl3 \
        libtool \
        libzip-dev \
        linux-headers \
        npm \
        openssh-client \
        openssl-dev \
        pkgconf \
        python3 \
        rsync \
        vim \
        wget \
        yarn \
        zlib-dev \
        zip \
# intl
    && docker-php-ext-install -j$(nproc) intl \
    && docker-php-ext-configure intl \
# mcrypt
    && pecl bundle -d /usr/src/php/ext mcrypt \
    && docker-php-ext-configure mcrypt \
# apcu
    && pecl bundle -d /usr/src/php/ext apcu \
    && docker-php-ext-install -j$(nproc) apcu \
# zip
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
# mysql
    && docker-php-ext-install mysqli \
# mongodb
    && pecl channel-update pecl.php.net \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
# other
    && docker-php-ext-configure gd \
    && docker-php-ext-install -j$(nproc) bcmath gd \
    && docker-php-ext-enable opcache \
# xdebug
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
#    && rm /usr/src/php/ext/*.tgz \
    && rm -rf /var/cache/apk/* \
    && rm -rf /tmp/* \
# ssl certs for nginx
# https://github.com/dunglas/symfony-docker/blob/master/Dockerfile
    && openssl genrsa -des3 -passout pass:NotSecure -out server.pass.key 2048 \
    && openssl rsa -passin pass:NotSecure -in server.pass.key -out server.key \
    && rm -f server.pass.key \
    && openssl req -new -passout pass:NotSecure -key server.key -out server.csr -subj '/C=SS/ST=SS/L=Gotham City/O=Symfony/CN=localhost' \
    && openssl x509 -req -sha256 -days 365 -in server.csr -signkey server.key -out server.crt \
# composer
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --filename=composer --install-dir=/usr/bin \
    && chmod +x /usr/bin/composer \
    && php -r "unlink('composer-setup.php');" \
    && composer --version \
# phpunit
    && composer global require "phpunit/phpunit" \
    && export PATH=$PATH:/root/.composer/vendor/bin \
    && ln -s /root/.composer/vendor/bin/phpunit /usr/bin/phpunit \
# bash-profile
    && git clone https://github.com/maciejzgadzaj/bash-profile.git \
    && rsync -av bash-profile/ ~ \
    && rm -rf bash-profile
