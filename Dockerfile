FROM php:8.2-apache

# Copy project files into the Apache server directory
COPY . /var/www/html/

# Ensure the data storage directory exists and is writable by Apache
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html

# Render assigns a dynamic port via environment variables, configure Apache to listen on it
RUN sed -i 's/80/8383/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 8383
