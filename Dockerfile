FROM php:8.2-apache

# Copy all project files into the Apache web root
COPY . /var/www/html/

# Create the data storage directory and grant permissions to Apache
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html

# Setup Apache to listen on Render's dynamic port environment variable
RUN echo "Listen 10000" >> /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost *:80>/<VirtualHost *:10000>/g' /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

# Run Apache in the foreground container loop
CMD ["apache2-foreground"]
