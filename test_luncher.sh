#!/bin/bash
set -e
cd `php -r "echo dirname(realpath('$0'));"`

if [ ! -f composer.phar ]; then
    echo "- download composer.phar"
    curl -s http://getcomposer.org/installer | php
fi

if [ ! -d vendor ]; then
    echo "- install dependencies"
    php composer.phar install
fi

echo "- drop database test"
php bin/console doctrine:database:drop --env=test --force || true

echo "- create database test"
php bin/console doctrine:database:create --env=test

echo "- create SQL schema"
php bin/console doctrine:schema:create --env=test

echo "- load fixtures in project test"
php bin/console doctrine:fixtures:load --env=test -n

echo "- TEST DRIVEN DEVLOPEMENT with option --testdox"
php phpunit.phar -c phpunit.xml.dist --testdox


