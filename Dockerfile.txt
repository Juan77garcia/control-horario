FROM php:8.1-apache

# Habilitar mod_rewrite (útil si usas .htaccess)
RUN a2enmod rewrite

# Copiar tu proyecto dentro del contenedor
COPY public/ /var/www/html/

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Cambiar permisos si es necesario
RUN chown -R www-data:www-data /var/www/html
