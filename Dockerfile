FROM composer as phpcomposer
WORKDIR /app
COPY composer.json composer.json
RUN composer install --ignore-platform-reqs

FROM php:7.2-apache
COPY --from=phpcomposer /app/vendor /var/www/html/vendor
COPY --from=phpcomposer /app/composer.json /var/www/html/composer.json
COPY --from=phpcomposer /app/composer.lock /var/www/html/composer.lock
COPY /src /var/www/html/src
COPY index.php /var/www/html/index.php
