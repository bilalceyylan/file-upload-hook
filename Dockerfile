FROM php:7.3-apache

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y git && \
    docker-php-ext-install mysqli && \
    apache2-foreground && \
    docker-php-ext-install pdo pdo_mysql pdo_pgsql
    
#install composer 
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
