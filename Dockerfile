FROM php:7.0-cli

RUN apt-get update \
    && apt-get install -y \
        librabbitmq-dev \
        libssh-dev \
    && docker-php-ext-install \
        opcache \
        bcmath \
        sockets \
    && pecl install \
        amqp \
    && docker-php-ext-enable \
        amqp \
    && mkdir /bench

WORKDIR /bench
