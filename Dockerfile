FROM php:8.2-apache

# Copy all project files into the Apache web root
COPY . /var/www/html/

# Create data directory and grant Apache write permissions
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html

# Configure Apache to listen on Render's required port 10000
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

# Start Apache in the foreground
CMD ["apache2-foreground"]
