FROM ubuntu:22.04
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y software-properties-common curl git unzip
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs
RUN add-apt-repository ppa:ondrej/php -y
RUN apt-get update && apt-get install -y php8.3 php8.3-cli php8.3-mysql php8.3-bcmath php8.3-zip php8.3-mbstring php8.3-xml php8.3-curl apache2 libapache2-mod-php8.3
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . .
RUN composer install --optimize-autoloader --no-scripts --no-interaction
RUN npm ci && npm run build
RUN chown -R www-data:www-data /var/www/html
RUN sed -i "s!/var/www/html!/var/www/html/public!g" /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN echo '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>' >> /etc/apache2/apache2.conf
RUN php artisan config:clear && php artisan view:clear || true
EXPOSE 80
CMD sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf && sed -i "s/:80/:${PORT:-80}/" /etc/apache2/sites-available/000-default.conf && apache2ctl -D FOREGROUND