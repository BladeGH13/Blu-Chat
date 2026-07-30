FROM php:8.2-apache

COPY . /var/www/html/

RUN mkdir -p /var/www/html/api/data && chown -R www-data:www-data /var/www/html

RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 10000
CMD ["apache2-foreground"]