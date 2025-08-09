#!/bin/sh

cd src
git checkout master
./vendor/bin/phpstan analyse --memory-limit=2G
./vendor/bin/phpunit
git checkout parf-edhellen-prod-v2
git pull
./vendor/bin/phpunit
npm run production
git checkout master
cd ..
