# On part de l'image de base
FROM php:apache

# On installe les pilotes pour que PHP puisse parler à la base de données
# (On installe pdo_mysql car j'utilise new PDO dans mon code !)
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli pdo_mysql

# On active le module de réécriture d'URL (bon réflexe à avoir)
RUN a2enmod rewrite
