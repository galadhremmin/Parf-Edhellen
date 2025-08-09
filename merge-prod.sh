#!/bin/sh

git checkout master
git push
git checkout parf-edhellen-prod-v2
git pull origin master
git push origin parf-edhellen-prod-v2
git checkout master
