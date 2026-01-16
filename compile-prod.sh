#!/bin/sh

cd src
git checkout master
./vendor/bin/phpstan analyse --memory-limit=2G
php artisan test
git checkout parf-edhellen-prod-v2
git pull
php artisan test
npm run production
git checkout master
cd ..
