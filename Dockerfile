FROM php:8.3-apache 
RUN apt-get update && apt-get install -y libzip-dev zip unzip git curl 
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - 
RUN apt-get install -y nodejs 
RUN docker-php-ext-install bcmath pdo pdo_mysql zip 
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer 
RUN a2enmod rewrite 
RUN a2dismod mpm_event && a2enmod mpm_prefork 
WORKDIR /var/www/html 
COPY . . 
RUN composer install --optimize-autoloader --no-scripts --no-interaction 
RUN npm ci && npm run build 
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf 
EXPOSE 80
