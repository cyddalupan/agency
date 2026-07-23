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

echo "--- Building frontend assets ---"
npm install
npm run build

echo "--- Restarting queue workers ---"
php artisan queue:restart

echo "=== Deployment complete ==="
