FROM php:8.4-cli

RUN apt-get update && apt-get install -y git redis-server \
    && curl -fsSL https://github.com/phpredis/phpredis/archive/6.1.0.tar.gz | tar xz -C /tmp \
    && cd /tmp/phpredis-6.1.0 && phpize && ./configure && make && make install \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/phpredis-6.1.0 \
    && apt-get clean

RUN curl -fsSL https://github.com/xdebug/xdebug/archive/3.4.1.tar.gz | tar xz -C /tmp \
    && cd /tmp/xdebug-3.4.1 && phpize && ./configure && make && make install \
    && docker-php-ext-enable xdebug \
    && rm -rf /tmp/xdebug-3.4.1

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /DATA
