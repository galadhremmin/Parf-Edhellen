#!/bin/sh

cd src
git checkout parf-edhellen-prod
npm run production
git checkout master
cd ..
