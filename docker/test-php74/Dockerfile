FROM php:7.4-cli

RUN apt-get update && apt-get install -y git unzip redis-server \
    && curl -fsSL https://github.com/phpredis/phpredis/archive/5.3.7.tar.gz | tar xz -C /tmp \
    && cd /tmp/phpredis-5.3.7 && phpize && ./configure && make && make install \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/phpredis-5.3.7 \
    && apt-get clean

RUN curl -fsSL https://github.com/xdebug/xdebug/archive/2.9.2.tar.gz | tar xz -C /tmp \
    && cd /tmp/xdebug-2.9.2 && phpize && ./configure && make && make install \
    && docker-php-ext-enable xdebug \
    && rm -rf /tmp/xdebug-2.9.2

RUN { echo 'xdebug.coverage_enable=1'; echo 'xdebug.mode=coverage'; } > /usr/local/etc/php/conf.d/99-xdebug-coverage.ini

# 安装 Composer 2.2.x（走官方源 getcomposer.org，不依赖其他 docker 镜像）
RUN curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php \
    && php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --version=2.2.21 \
    && rm /tmp/composer-setup.php

WORKDIR /DATA
