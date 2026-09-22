@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title WestBridge Technologies - move data to MySQL

echo.
echo   ================================================================
echo    Move the website's data into MySQL (XAMPP)
echo   ================================================================
echo.
echo    This copies everything - products, projects, team, news,
echo    messages, settings and staff accounts - into a new MySQL
echo    database called "westbridge", checks every table, and only
echo    then switches the site over.
echo.
echo    Your current data file is NOT changed. It stays as a backup.
echo.
echo    Before you continue:
echo      1. Open the XAMPP Control Panel
echo      2. Click Start next to MySQL (it should turn green)
echo.
pause

if not exist "westbridge\artisan" (
    echo   [X] The site is not installed yet. Run RUN-ME.bat first.
    pause
    exit /b 1
)

php -r "exit(@fsockopen('127.0.0.1', 3306) ? 0 : 1);"
if errorlevel 1 (
    echo.
    echo   [X] MySQL is not running. Start it in the XAMPP Control Panel,
    echo       then double-click this file again.
    echo.
    pause
    exit /b 1
)

cd westbridge
call php artisan config:clear >nul 2>&1
call php artisan migrate --force >nul 2>&1
call php artisan wb:move-to-mysql
set "RESULT=%ERRORLEVEL%"

echo.
if "%RESULT%"=="0" (
    echo   ================================================================
    echo    Finished. Open http://localhost/phpmyadmin - the database
    echo    "westbridge" is on the left.
    echo.
    echo    From now on keep MySQL started in XAMPP whenever you run
    echo    the site. RUN-ME.bat will remind you if it is not.
    echo   ================================================================
) else (
    echo   Nothing was switched. The site still uses its original data.
    echo   Copy the message above into the chat if you need help.
)
echo.
pause
exit /b %RESULT%
