#!/bin/sh

cd src
git checkout master
./vendor/bin/phpstan analyse --memory-limit=2G
php artisan test
git checkout parf-edhellen-prod-v2
# --ff-only overrides this repo's pull.ff=no, which would otherwise merge on every pull
git pull --ff-only
php artisan test
npm run production
git checkout master
cd ..
