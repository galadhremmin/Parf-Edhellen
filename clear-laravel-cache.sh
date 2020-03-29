#!/bin/bash

cd src
php artisan view:clear
rm -fv storage/framework/views/*.php
rm -fv storage/framework/cache/data/*
cd ..
