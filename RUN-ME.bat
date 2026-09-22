@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title WestBridge Technologies - setup and run
color 0B

echo.
echo   ================================================================
echo    WESTBRIDGE TECHNOLOGIES
echo    Connecting Ideas. Building Tomorrow.
echo   ================================================================
echo.
echo    This window will set up the website and then start it.
echo    The first run downloads a lot and can take 10-20 minutes.
echo    Leave it open until it says the server is running.
echo.

REM ---------------------------------------------------------------------------
REM  Prerequisites
REM ---------------------------------------------------------------------------
set "MISSING="

where php >nul 2>&1        || set "MISSING=1"
where composer >nul 2>&1   || set "MISSING=1"
where npm >nul 2>&1        || set "MISSING=1"

if defined MISSING goto NEEDHERD

php "%~dp0scripts\check-env.php" --sqlite
if errorlevel 1 goto FAIL

REM ---------------------------------------------------------------------------
REM  Install, unless it is already installed
REM ---------------------------------------------------------------------------
REM Test for a file only the WestBridge overlay creates. "artisan" is NOT a
REM valid marker: composer create-project writes it, so a half-finished run
REM would look complete and start an untouched Laravel instead.
if exist "westbridge\config\westbridge.php" (
    echo   Already set up - starting the site.
    echo.
    goto RUN
)

echo   Setting up. This resumes where it stopped - nothing already
echo   downloaded will be downloaded again.
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0install.ps1" -SkipCheck

if errorlevel 1 (
    echo.
    echo   [X] Setup did not finish. The error is above this line.
    echo       Copy it into the chat and I will sort it out.
    echo.
    goto FAIL
)

if not exist "westbridge\config\westbridge.php" (
    echo.
    echo   [X] Setup reported success but the WestBridge files are missing.
    echo       Copy whatever is above this line into the chat.
    echo.
    goto FAIL
)

REM ---------------------------------------------------------------------------
REM  Run
REM ---------------------------------------------------------------------------
:RUN
cd westbridge

REM When the site uses MySQL, XAMPP's MySQL must be running first.
findstr /b /c:"DB_CONNECTION=mysql" .env >nul 2>&1
if not errorlevel 1 (
    php -r "exit(@fsockopen('127.0.0.1', 3306) ? 0 : 1);"
    if errorlevel 1 (
        echo   [X] MySQL is not running. The site keeps its data in MySQL now.
        echo       Open the XAMPP Control Panel, click Start next to MySQL,
        echo       then double-click RUN-ME.bat again.
        echo.
        goto FAIL
    )
)

REM ---------------------------------------------------------------------------
REM  Bring the installed copy up to date with any new features. Every step
REM  here is safe to repeat: it only adds what is missing.
REM ---------------------------------------------------------------------------
if not exist "vendor\laravel\sanctum" (
    echo   Adding API sign-in support - one time only, needs internet...
    call composer require laravel/sanctum --no-interaction
    if errorlevel 1 (
        echo   [!] Could not install it. The website still works; only API
        echo       sign-in is unavailable until this succeeds on a later run.
    )
    echo.
)

if not exist "vendor\symfony\html-sanitizer" (
    echo   Adding the page-text safety filter - one time only, needs internet...
    call composer require symfony/html-sanitizer --no-interaction
    echo.
)

REM Laravel ships a static robots.txt that would hide the site's own one.
if exist "public\robots.txt" del /q "public\robots.txt"
if not exist "public\uploads" mkdir "public\uploads"

echo   Updating the database...
call php artisan migrate --force
if errorlevel 1 (
    echo   [X] The database update failed. The error is above this line.
    echo       Copy it into the chat.
    goto FAIL
)
REM Seeders are create-only: they add new categories, settings and projects
REM and never overwrite anything edited in the admin panel.
call php artisan db:seed --force >nul
REM The local-only test account (admin@westbridge.test / password). This
REM seeder refuses to run anywhere except APP_ENV=local.
call php artisan db:seed --class=LocalAdminSeeder --force >nul
call php artisan optimize:clear >nul 2>&1
echo   Done.
echo.

REM Rebuild the CSS and JS. Blade templates are read from disk on every
REM request, but app.css and app.js are compiled - editing them does nothing
REM until this runs. A few seconds here beats wondering why a change did not
REM show up.
echo   Rebuilding the stylesheet...
call npm run build --silent >nul 2>&1
if errorlevel 1 (
    echo   [!] The rebuild failed. The site will still start, using the
    echo       previously built stylesheet.
    echo.
) else (
    echo   Done.
    echo.
)


echo   ================================================================
echo    Starting the website.
echo.
echo    Your browser will open at  http://127.0.0.1:8000
echo    If it does not, type that address in yourself.
echo.
echo    Admin panel:  http://127.0.0.1:8000/admin
echo    Local sign-in: admin@westbridge.test  /  password
echo.
echo    Leave THIS window open while you use the site.
echo    Press Ctrl+C here to stop it.
echo   ================================================================
echo.

REM Background worker that sends queued email (it opens minimised; closing
REM it only stops email). On your PC email is written to the log file.
start "WestBridge email worker" /min php artisan queue:work --sleep=3 --tries=3

REM Open the browser a few seconds after the server has had time to bind.
start "" /b powershell -NoProfile -Command "Start-Sleep -Seconds 5; Start-Process 'http://127.0.0.1:8000'"

php artisan serve

echo.
echo   Server stopped.
pause
exit /b 0

REM ---------------------------------------------------------------------------
:NEEDHERD
echo   [X] PHP, Composer or Node is not installed on this PC.
echo.
echo       The easiest fix is Laravel Herd - one free installer that
echo       gives you all three. Opening the download page now.
echo.
echo       After installing Herd:
echo         1. CLOSE this window
echo         2. Close any other PowerShell or Command Prompt windows
echo         3. Double-click RUN-ME.bat again
echo.
echo       Step 2 matters - new programs are not visible to windows
echo       that were already open.
echo.
start "" https://herd.laravel.com/windows
pause
exit /b 1

REM ---------------------------------------------------------------------------
:FAIL
pause
exit /b 1
