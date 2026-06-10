FROM php:7.4-cli

RUN apt-get update && apt-get install -y git redis-server \
    && curl -fsSL https://github.com/phpredis/phpredis/archive/5.3.7.tar.gz | tar xz -C /tmp \
    && cd /tmp/phpredis-5.3.7 && phpize && ./configure && make && make install \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/phpredis-5.3.7 \
    && apt-get clean

RUN curl -fsSL https://github.com/xdebug/xdebug/archive/2.9.2.tar.gz | tar xz -C /tmp \
    && cd /tmp/xdebug-2.9.2 && phpize && ./configure && make && make install \
    && docker-php-ext-enable xdebug \
    && rm -rf /tmp/xdebug-2.9.2

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

WORKDIR /DATA
