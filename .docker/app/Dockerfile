FROM php:fpm
RUN pecl install xdebug-3.1.3 \
    && docker-php-ext-enable xdebug
COPY config/php.ini /usr/local/etc/php/conf.d/
COPY --from=composer /usr/bin/composer /usr/bin/composer