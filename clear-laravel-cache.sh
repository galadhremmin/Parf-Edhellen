#!/bin/bash

cd src
php artisan view:clear
php artisan cache:clear
rm -fv storage/framework/views/*.php
rm -frv storage/framework/cache/data/*
php artisan optimize
cd ..
