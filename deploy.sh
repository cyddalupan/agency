#!/bin/bash
set -e

DEPLOY_PATH="/var/www/agency.classapparelph.com"
BRANCH="master"

echo "=== Agency Super Deployment ==="
echo "Target: $DEPLOY_PATH ($BRANCH)"
date

cd "$DEPLOY_PATH"

echo "--- Pulling latest code ---"
git pull origin "$BRANCH"

echo "--- Installing PHP dependencies ---"
composer install --no-dev --optimize-autoloader

echo "--- Running migrations ---"
php artisan migrate --force

echo "--- Clearing old caches ---"
php artisan optimize:clear

echo "--- Caching config ---"
php artisan config:cache

echo "--- Caching routes ---"
php artisan route:cache

echo "--- Caching views ---"
php artisan view:cache

echo "--- Fixing cache permissions ---"
# The cache commands above run as root, so any file-cache prefix dir they
# create is born 0755 (root's umask 0022) and the web user cannot create the
# nested subdirs inside it — that was the login-time 500 this guards against.
# Relative to the script's cwd so it works on whichever host runs it.
if [ -d storage/framework/cache/data ]; then
    find storage/framework/cache/data -mindepth 1 -maxdepth 1 -type d -exec chmod 2775 {} +
fi
chown -R www-data:www-data storage/framework

echo "--- Building frontend assets ---"
npm install
npm run build

echo "--- Restarting queue workers ---"
php artisan queue:restart

echo "=== Deployment complete ==="
