# Use PHP 8.1 with Apache
FROM php:8.1-apache

# Install required PHP extensions for LibreKB (mysqli, PDO, and PDO MySQL)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite for pretty URLs
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy LibreKB source code into the container
COPY . /var/www/html/

# Ensure Apache can access and write to the files
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Run Apache in the foreground
CMD ["apache2-foreground"]
