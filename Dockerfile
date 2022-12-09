ARG PHP_VERSION=7.3
FROM php:$PHP_VERSION-cli-alpine

RUN apk add git zip unzip autoconf make g++ bash vim


RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

WORKDIR /package

RUN adduser -D -g '' dev

RUN chown dev -R /package

USER dev

COPY --chown=dev composer.json ./

ARG LARAVEL=7
RUN composer require laravel/framework ^$LARAVEL.0

COPY --chown=dev . .

RUN composer test
