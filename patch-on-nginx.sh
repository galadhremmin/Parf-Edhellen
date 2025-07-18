#!/bin/bash

sudo -u www-data git pull
cd src
if ! [ -z "$1" ]
  then
    mv -v $1 public/
fi
sudo chown -R www-data:www-data .
sudo -u www-data composer update
sudo -u www-data php artisan migrate
sudo -u www-data rm -f storage/framework/views/*.php
sudo -u www-data php artisan optimize
cd ..
