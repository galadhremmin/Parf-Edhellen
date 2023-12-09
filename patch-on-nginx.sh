#!/bin/bash

sudo git pull
cd src
if ! [ -z "$1" ]
  then
    mv -v $1 public/
fi
sudo chown -R nginx:nginx .
sudo -u nginx composer update
sudo -u nginx php artisan migrate
sudo -u nginx php artisan optimize
cd ..
