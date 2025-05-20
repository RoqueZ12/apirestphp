# Imagen base con PHP 8.2
FROM php:8.2-cli

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear directorio de trabajo
WORKDIR /app

# Copiar todo el contenido del proyecto
COPY . .

# Instalar dependencias de Composer
RUN composer install

# Exponer el puerto para Render
EXPOSE 8080

# Comando para ejecutar el servidor PHP desde el index.php en la raíz
CMD ["php", "-S", "0.0.0.0:8080", "index.php"]
