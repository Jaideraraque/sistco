FROM php:8.3-cli 
RUN apt-get update && apt-get install -y libzip-dev zip unzip git curl 
RUN docker-php-ext-install bcmath pdo pdo_mysql zip 
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer 
WORKDIR /app 
COPY . . 
RUN composer install --optimize-autoloader --no-scripts --no-interaction 
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
