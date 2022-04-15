#!/bin/sh

cd src
./vendor/phpunit/phpunit/phpunit
git checkout parf-edhellen-prod
npm run production
git checkout master
cd ..
