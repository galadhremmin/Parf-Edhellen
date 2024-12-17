#!/bin/bash

cd ../src
php artisan config:clear # force refresh
php artisan migrate
php artisan optimize
cd ../model
