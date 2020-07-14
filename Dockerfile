#LABEL maintainer="j.koehler@outlook.com"

FROM php:7.4-apache-buster
RUN apt-get update && apt-get install -y \
    libzip-dev libjpeg-dev libpq-dev libjpeg62-turbo-dev libpng-dev libicu-dev nano git apt-utils zip libfreetype6-dev \
 && rm -rf /var/lib/apt/lists/*

RUN  docker-php-ext-configure gd \
            --with-freetype \
            --with-jpeg

RUN docker-php-ext-configure zip

RUN docker-php-ext-install pdo_mysql intl zip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

ENV APP_ENV=prod
ENV HTTPDUSER='www-data'

EXPOSE 8080

#COPY --from=build /app/vendor /var/www/html/vendor
COPY . /var/www/html/
RUN rm -f /var/www/html/.env.local

RUN chown -R www-data:www-data /var/www/  && \
    a2enmod rewrite && \
    echo "Listen 8080" > /etc/apache2/ports.conf && \
    sed -i '/</,/>/ s/*:80/*:8080/' /etc/apache2/sites-enabled/000-default.conf

RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride all/' /etc/apache2/apache2.conf && \
    sed -i '/<VirtualHost/,/<\/VirtualHost>/ s/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/html\/public/' /etc/apache2/sites-enabled/000-default.conf

USER www-data

RUN composer install
RUN php bin/console cache:clear --no-warmup && \
    php bin/console cache:warmup

CMD ["apache2-foreground"]