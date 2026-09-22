#!/usr/bin/env bash
#
# WestBridge Technologies - Phase 1 installer
#
# Creates a fresh Laravel 12 application, installs the Phase 1 dependencies and
# copies the WestBridge source overlay over the top.
#
# Usage:  ./install.sh [target-directory]      (default: westbridge)
#
set -euo pipefail

APP_DIR="${1:-westbridge}"
HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Local development runs on SQLite by default - no database server to install.
# Pass --mysql to use MySQL instead (what production uses).
USE_SQLITE=1
for arg in "$@"; do
    [ "$arg" = "--mysql" ] && USE_SQLITE=""
done

say() { printf '\n\033[1;32m==>\033[0m %s\n' "$1"; }
die() { printf '\n\033[1;31mError:\033[0m %s\n' "$1" >&2; exit 1; }

command -v php >/dev/null || die "PHP is not installed."
command -v composer >/dev/null || die "Composer is not installed."
command -v npm >/dev/null || die "Node/npm is not installed."

php "$HERE/scripts/check-env.php" ${USE_SQLITE:+--sqlite} || exit 1

[ -e "$APP_DIR" ] && die "Directory '$APP_DIR' already exists."

say "Creating Laravel 12 application in ./$APP_DIR"
composer create-project "laravel/laravel:12.*" "$APP_DIR" --no-interaction

cd "$APP_DIR"

say "Installing runtime dependencies"
composer require --no-interaction livewire/livewire spatie/laravel-permission propaganistas/laravel-phone laravel/sanctum symfony/html-sanitizer

say "Installing development dependencies"
composer require --dev --no-interaction larastan/larastan pestphp/pest pestphp/pest-plugin-laravel

say "Publishing the permission migrations"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --quiet

say "Installing front-end dependencies"
npm install --silent
npm install --silent tailwindcss@4 "@tailwindcss/vite@4" "@fontsource-variable/jost" "@fontsource/ibm-plex-sans" "@fontsource/ibm-plex-mono"

say "Applying the WestBridge overlay"
cp -R "$HERE/overlay/." .

say "Wiring everything together"
php "$HERE/scripts/postinstall.php" ${USE_SQLITE:+--sqlite}
composer dump-autoload --quiet

say "Generating the application key"
php artisan key:generate --ansi
php artisan storage:link --quiet || true

say "Creating the database tables and seeding content"
[ -z "$USE_SQLITE" ] && echo "  (make sure the database in .env exists and is reachable)"
php artisan migrate --seed --force
php artisan db:seed --class=LocalAdminSeeder --force

say "Building assets"
npm run build

cat <<'DONE'

  ----------------------------------------------------------------
   Phase 1 installed.

   Start the application:   php artisan serve
   Then open:               http://127.0.0.1:8000

   Local admin login (local environment only):
     admin@westbridge.test / password

   Before anything goes on a public server, fill in the blank
   settings - phone, WhatsApp, email, address, hours, TIN and the
   sales-tax rate. They are seeded empty on purpose; every element
   that uses one stays hidden until it is set.
  ----------------------------------------------------------------

DONE
