FROM php:7.1.2-apache
ADD 000-default.conf /etc/apache2/sites-enabled/000-default.conf
RUN mkdir /var/www/html/Frontend/ && mkdir /var/www/html/Frontend/log
RUN a2enmod rewrite
RUN docker-php-ext-install mysqli