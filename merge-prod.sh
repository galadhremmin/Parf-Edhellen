#!/bin/sh

git checkout master
git push
git checkout parf-edhellen-prod
git pull origin master
git push origin parf-edhellen-prod
git checkout master
