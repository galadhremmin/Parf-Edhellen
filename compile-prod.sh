#!/bin/sh

cd src
git checkout parf-edhellen-prod
./vendor/phpunit/phpunit/phpunit
npm run production
git checkout master
cd ..
