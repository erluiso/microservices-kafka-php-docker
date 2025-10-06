FROM php:7.4-apache
RUN apt-get update
RUN apt-get install curl -y
RUN a2enmod rewrite
RUN apt-get install -y librdkafka-dev
RUN pecl install rdkafka
RUN echo "extension=rdkafka.so" > /usr/local/etc/php/conf.d/rdkafka.ini
RUN /etc/init.d/apache2 restart