<#
    WestBridge Technologies - Windows installer (Phases 1 & 2)

    Creates a fresh Laravel 12 application, installs the dependencies and
    copies the WestBridge source over the top.

    Usage, from PowerShell in this folder:

        .\install.ps1                 # creates .\westbridge, uses SQLite
        .\install.ps1 -Name my-site   # different folder name
        .\install.ps1 -Mysql          # use MySQL instead of SQLite

    If PowerShell refuses to run the script, allow local scripts for this
    window only:

        Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass

    Safe to run again after a failure: it resumes from wherever it stopped and
    does not re-download Laravel.

    NOTE FOR MAINTAINERS - two Windows traps this file has already hit:
      1. Keep this file pure ASCII. Windows PowerShell reads .ps1 as ANSI, so
         a curly quote or an em dash becomes mojibake and breaks the parser.
      2. Never put a caret in an argument. composer and npm on Windows are
         .bat/.cmd shims, so arguments get re-parsed by cmd.exe, which eats
         the caret: "pkg:^3.5" arrives as "pkg:3.5", an exact version pin.
#>

param(
    [string]$Name = "westbridge",
    [switch]$Mysql,
    [switch]$SkipCheck
)

$ErrorActionPreference = "Stop"
$here = Split-Path -Parent $MyInvocation.MyCommand.Path

function Say($message)  { Write-Host "`n==> $message" -ForegroundColor Green }
function Note($message) { Write-Host "    $message" -ForegroundColor DarkGray }
function Die($message)  { Write-Host "`nError: $message" -ForegroundColor Red; exit 1 }

function Have($command) {
    return [bool](Get-Command $command -ErrorAction SilentlyContinue)
}

# ---------------------------------------------------------------------------
# Prerequisites
# ---------------------------------------------------------------------------
if (-not $SkipCheck) {
    Say "Checking what is installed"

    if (-not (Have php)) {
        Die "PHP was not found.`n    Install Laravel Herd (free) from https://herd.laravel.com/windows`n    then close and reopen PowerShell and run this again."
    }
    if (-not (Have composer)) {
        Die "Composer was not found.`n    Get it from https://getcomposer.org/download/"
    }
    if (-not (Have npm)) {
        Die "Node/npm was not found.`n    Get Node 20 or newer from https://nodejs.org/"
    }
    Note "Node $(& node -v)"

    if ($Mysql) { & php (Join-Path $here "scripts\check-env.php") }
    else        { & php (Join-Path $here "scripts\check-env.php") --sqlite }
    if ($LASTEXITCODE -ne 0) { exit 1 }
}

$appDir = Join-Path $here $Name

# ---------------------------------------------------------------------------
# Create the application, or resume into one that is already there
# ---------------------------------------------------------------------------
if (Test-Path (Join-Path $appDir "artisan")) {
    Say "Found an existing application - resuming"
    Note "Laravel will not be downloaded again."
}
elseif (Test-Path $appDir) {
    Die "The folder '$Name' exists but does not contain a Laravel application.`n    Delete it and run this again."
}
else {
    Say "Creating the Laravel 12 application (this downloads a lot - be patient)"
    Set-Location $here
    & composer create-project "laravel/laravel:12.*" $Name --no-interaction
    if ($LASTEXITCODE -ne 0) { Die "composer create-project failed." }
}

Set-Location $appDir

# ---------------------------------------------------------------------------
# Dependencies
#
# Versions are deliberately NOT pinned here. Composer resolves the newest
# release compatible with the Laravel version just installed, which is what we
# want. Hand-pinned minor versions produced packages that predated Laravel 12
# support and could not resolve at all.
# ---------------------------------------------------------------------------
Say "Installing PHP packages"
& composer require --no-interaction livewire/livewire spatie/laravel-permission propaganistas/laravel-phone laravel/sanctum symfony/html-sanitizer
if ($LASTEXITCODE -ne 0) { Die "composer require failed." }

Say "Installing development packages"
& composer require --dev --no-interaction larastan/larastan pestphp/pest pestphp/pest-plugin-laravel
if ($LASTEXITCODE -ne 0) { Die "composer require --dev failed." }

Say "Publishing the permission migrations"
& php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --quiet

Say "Installing front-end packages"
& npm install --no-fund --no-audit
if ($LASTEXITCODE -ne 0) { Die "npm install failed." }

# "@4" rather than "@^4" - see the caret note at the top of this file.
& npm install --no-fund --no-audit tailwindcss@4 "@tailwindcss/vite@4" "@fontsource-variable/jost" "@fontsource/ibm-plex-sans" "@fontsource/ibm-plex-mono"
if ($LASTEXITCODE -ne 0) { Die "npm install of the front-end packages failed." }

# ---------------------------------------------------------------------------
# WestBridge source
# ---------------------------------------------------------------------------
Say "Applying the WestBridge source"
Copy-Item -Path (Join-Path $here "overlay\*") -Destination $appDir -Recurse -Force
Note "Overlay copied"

Say "Wiring everything together"
$postinstall = Join-Path $here "scripts\postinstall.php"
if ($Mysql) { & php $postinstall }
else        { & php $postinstall --sqlite }
if ($LASTEXITCODE -ne 0) { Die "Wiring failed." }

& composer dump-autoload --quiet

Say "Checking the application key"
if (-not (Select-String -Path ".env" -Pattern "^APP_KEY=base64:" -Quiet)) {
    & php artisan key:generate --ansi
} else {
    Note "Already set"
}

& php artisan storage:link 2>$null

# ---------------------------------------------------------------------------
# Database
# ---------------------------------------------------------------------------
Say "Creating the database tables and seeding content"
if ($Mysql) { Note "Make sure the database named in .env exists and is reachable." }

& php artisan migrate --seed --force
& php artisan db:seed --class=LocalAdminSeeder --force
if ($LASTEXITCODE -ne 0) {
    Die "Migration failed. If you used -Mysql, check the DB_ settings in .env and that MySQL is running."
}

Say "Building the CSS and JavaScript"
& npm run build
if ($LASTEXITCODE -ne 0) { Die "npm run build failed." }

# ---------------------------------------------------------------------------
# Done
# ---------------------------------------------------------------------------
Write-Host @"

  ================================================================
   Installed.

   Start it:
       cd $Name
       php artisan serve

   Then open  http://127.0.0.1:8000  in your browser.

   Run the tests (this proves the quote form creates a lead):
       php artisan test

   Admin login, local only:
       admin@westbridge.test / password

   Contact details are deliberately blank. To fill one in:
       php artisan tinker
       then: setting_service()->set('contact.whatsapp', '+211912345678');

   Press Ctrl+C in the terminal to stop the server.
  ================================================================

"@ -ForegroundColor Cyan
