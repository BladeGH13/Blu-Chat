FROM php:8.2-apache

# Copy source code
COPY . /var/www/html/

# Ensure data directory has proper write permissions
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html

# Overwrite Apache ports and virtual host configurations cleanly
COPY apache-port.conf /etc/apache2/ports.conf
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

CMD ["apache2-foreground"]
