FROM php:8.1-apache

# Instalar extensiones necesarias (mysqli)
RUN docker-php-ext-install mysqli

# Habilitar mod_rewrite por si usas .htaccess
RUN a2enmod rewrite

# Copiar los archivos del proyecto
COPY public/ /var/www/html/

# Establecer permisos correctos
RUN chown -R www-data:www-data /var/www/html
