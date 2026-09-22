#!/usr/bin/env bash
# WestBridge - update the live site. Run from the app folder on the server.
#   bash scripts/deploy.sh             normal update
#   bash scripts/deploy.sh --first-run first install
#
# Order matters: preflight -> backup -> maintenance on -> update -> checks ->
# maintenance off. If ANY step fails the script stops and the site stays in
# maintenance mode, so visitors never see a half-updated site.
set -euo pipefail
cd "$(dirname "$0")/.."

FIRST=0
[[ "${1:-}" == "--first-run" ]] && FIRST=1

say()  { printf '\n\033[1;32m==>\033[0m %s\n' "$1"; }
fail() { printf '\n\033[1;31mDEPLOY STOPPED:\033[0m %s\n' "$1" >&2; }

on_error() {
    fail "a step failed (line $1). The site is still in maintenance mode."
    echo "Fix the problem, then run this script again, or restore the backup made above."
    echo "To bring the site back unchanged: php artisan up"
}
trap 'on_error $LINENO' ERR

if [[ ! -f .env ]]; then
    fail "no .env file. Copy .env.example to .env and fill it in first (docs/DEPLOYMENT.md)."
    exit 1
fi

if command -v composer >/dev/null 2>&1; then
    say "Installing PHP packages"
    composer install --no-dev --optimize-autoloader --no-interaction
fi

if [[ $FIRST -eq 1 ]] && ! grep -q '^APP_KEY=base64' .env; then
    say "Generating the application key"
    php artisan key:generate --force
fi

php artisan config:clear >/dev/null

say "Checking the configuration"
php artisan wb:preflight

if [[ $FIRST -eq 0 ]]; then
    say "Backing up the database before changing anything"
    php artisan wb:backup --label=pre-deploy --keep=10

    say "Maintenance mode on"
    php artisan down --retry=30
fi

# Laravel's static robots.txt would hide the site's own (which lists the sitemap).
rm -f public/robots.txt
mkdir -p public/uploads storage/app/backups storage/framework/{cache,sessions,views} storage/logs bootstrap/cache

say "Updating the database"
php artisan migrate --force
php artisan db:seed --force

say "Rebuilding caches"
php artisan optimize:clear
php artisan optimize
php artisan view:cache

chmod -R ug+rwX storage bootstrap/cache public/uploads 2>/dev/null || true

say "Final check"
php artisan wb:preflight

trap - ERR
php artisan up
say "Done - the site is live."
[[ $FIRST -eq 1 ]] && echo "Next: php artisan wb:create-admin, then add the cron job (docs/DEPLOYMENT.md)."
exit 0
