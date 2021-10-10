#!/usr/bin/env bash

# Composer install
composer install -d project

composer install -d shopware-app --no-interaction
composer install -d shopware-app/vendor/shopware/recovery --no-interaction --no-scripts
composer install -d shopware-app/vendor/shopware/recovery/Common --no-interaction --optimize-autoloader --no-suggest

# Basic setup and some demo data, will be replaced as the project grows
shopware-app/bin/console system:install --create-database --basic-setup --force
shopware-app/bin/console framework:demodata

# Generate JWT secrets for API communication
shopware-app/bin/console system:generate-jwt-secret -f

# Change owner to apache user
chown -R www-data:www-data ./

# build admin & storefront
shopware-app/bin/build-js.sh

# generate data transfer objects
#APPLICATION_PATH=project project/bin/console dataprovider:generate -v

#shopware-app/vendor/bin/phpunit -c shopware-app/phpunit.xml --testdox
