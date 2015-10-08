#!/bin/bash

# Setup PHP-FPM
echo "Configuring php-fpm"

PHP_FPM_BIN="~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm"
PHP_FPM_CONF="~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf"
PHP_FPM_SOCK="/var/run/php-fpm.sock"
PHP_FPM_LOG="$TRAVIS_BUILD_DIR/php-fpm.log"

USER=$(whoami)

echo "php-fpm user = $USER"

sudo touch "$PHP_FPM_LOG"

# Adjust php-fpm.ini
sed -i "s/@USER@/$USER/g" "$TRAVIS_BUILD_DIR/.travis/php-fpm.ini"
sed -i "s|@PHP_FPM_SOCK@|$PHP_FPM_SOCK|g" "$TRAVIS_BUILD_DIR/.travis/php-fpm.ini"
sed -i "s|@PHP_FPM_LOG@|$PHP_FPM_LOG|g" "$TRAVIS_BUILD_DIR/.travis/php-fpm.ini"
sed -i "s|@PATH@|$PATH|g" "$TRAVIS_BUILD_DIR/.travis/php-fpm.ini"

# Start daemon
echo "Starting php-fpm"
sudo ls -R "/home/travis/.phpenv/versions/$(phpenv version-name)/"
sudo $PHP_FPM_BIN --fpm-config "$TRAVIS_BUILD_DIR/.travis/php-fpm.ini"
sudo chown www-data:www-data /var/run/php-fpm.sock