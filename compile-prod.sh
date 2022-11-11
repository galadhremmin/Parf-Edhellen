#!/bin/sh

cd src
git checkout parf-edhellen-prod
git pull
./vendor/phpunit/phpunit/phpunit
npm run production
git checkout master
cd ..
