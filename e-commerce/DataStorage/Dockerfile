FROM php:8.1.0-apache

RUN apt-get update && apt-get install libssl-dev --yes
RUN pecl install mongodb && docker-php-ext-enable mongodb
