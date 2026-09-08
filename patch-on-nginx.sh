#!/bin/bash
set -euo pipefail

trap 'echo "Deployment failed. The site is still in maintenance mode -- bring it back with: sudo -u www-data php src/artisan up" >&2' ERR

# Parse the arguments. Both are optional, and may be given in any order: --version=X sets
# ED_VERSION in .env, and the remaining argument is the compiled asset directory to publish.
ED_VERSION=""
ASSETS=""
while [ $# -gt 0 ]; do
  case "$1" in
    --version=*)
      ED_VERSION="${1#*=}"
      ;;
    *)
      ASSETS="$1"
      ;;
  esac
  shift
done

# The version ends up in a sed replacement and in a path, so keep it to harmless characters.
if [ -n "$ED_VERSION" ] && ! [[ "$ED_VERSION" =~ ^[A-Za-z0-9._-]+$ ]]; then
  echo "Refusing to deploy: '${ED_VERSION}' is not a valid ED_VERSION." >&2
  exit 1
fi

# Take the site down first, so that no request is served while the database, the assets and the
# caches are out of step with each other. The application may be unbootable, hence the fallback.
sudo -u www-data php src/artisan down --retry=60 || true

sudo -u www-data git pull --ff-only
cd src

# Update ED_VERSION in .env if --version was provided. This has to happen before the caches are
# rebuilt below, as `artisan optimize` bakes the environment into the configuration cache.
if [ -n "$ED_VERSION" ]; then
  if grep -q "^ED_VERSION=" .env 2>/dev/null; then
    sed -i "s/^ED_VERSION=.*/ED_VERSION=${ED_VERSION}/" .env
  else
    echo "ED_VERSION=${ED_VERSION}" >> .env
  fi
fi

if [ -n "$ASSETS" ]; then
  # Deploying the same version twice would otherwise nest the new directory inside the old one.
  rm -rfv "public/$(basename "$ASSETS")"
  mv -v "$ASSETS" public/
fi

# The asset paths are derived from ED_VERSION, so a mismatch here serves a site without styles
# or scripts. Better to stop while the site is still in maintenance mode.
if [ -n "$ED_VERSION" ] && [ ! -d "public/v${ED_VERSION}" ]; then
  echo "Refusing to deploy: ED_VERSION is ${ED_VERSION}, but public/v${ED_VERSION} does not exist." >&2
  exit 1
fi

sudo chown -R www-data:www-data .
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan optimize

# Restarting php-fpm discards the opcache, which would otherwise keep serving the previous code.
sudo systemctl restart php8.2-fpm
sudo systemctl restart parf-edhellen*

cd ..
sudo -u www-data php src/artisan up
