#!/bin/bash

# Parse --version=X argument
for arg in "$@"; do
  case $arg in
    --version=*)
      ED_VERSION="${arg#*=}"
      shift
      ;;
  esac
done

sudo -u www-data git pull
cd src

# Update ED_VERSION in .env if --version was provided
if [ -n "$ED_VERSION" ]; then
  if grep -q "^ED_VERSION=" .env 2>/dev/null; then
    sed -i "s/^ED_VERSION=.*/ED_VERSION=${ED_VERSION}/" .env
  else
    echo "ED_VERSION=${ED_VERSION}" >> .env
  fi
fi

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
