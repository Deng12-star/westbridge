@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title WestBridge Technologies - pre-deployment check

rem Read-only check before going live. It does not change your data or settings.
rem Everything is written to predeploy-results.txt next to this file.

set "OUT=%~dp0predeploy-results.txt"

if not exist "westbridge\artisan" (
    echo   [X] The site is not installed yet. Run RUN-ME.bat first.
    pause
    exit /b 1
)

echo.
echo   Running the pre-deployment check. This takes two or three minutes.
echo   Nothing is changed - it only reads and tests.
echo.

cd westbridge
echo WestBridge pre-deployment check - %DATE% %TIME% > "%OUT%"

echo   [1/8] Environment...
echo. >> "%OUT%" & echo ===== 1. ENVIRONMENT ===== >> "%OUT%"
call php artisan about --only=environment,drivers --no-ansi >> "%OUT%" 2>&1

echo   [2/8] Database migrations...
echo. >> "%OUT%" & echo ===== 2. MIGRATIONS (all should say Ran) ===== >> "%OUT%"
call php artisan migrate:status --no-ansi >> "%OUT%" 2>&1
call php artisan migrate:status --pending --no-ansi 2>nul | findstr /i "Pending" >nul && echo PENDING MIGRATIONS FOUND - run RUN-ME.bat >> "%OUT%"

echo   [3/8] Compiling every page template...
echo. >> "%OUT%" & echo ===== 3. VIEW COMPILE ===== >> "%OUT%"
call php artisan view:cache --no-ansi >> "%OUT%" 2>&1 && (echo VIEWS OK >> "%OUT%") || (echo VIEWS FAILED >> "%OUT%")
call php artisan view:clear --no-ansi >nul 2>&1

echo   [4/8] Routes and config caching (as on the live server)...
echo. >> "%OUT%" & echo ===== 4. ROUTE + CONFIG CACHE ===== >> "%OUT%"
call php artisan route:cache --no-ansi >> "%OUT%" 2>&1 && (echo ROUTES OK >> "%OUT%") || (echo ROUTES FAILED >> "%OUT%")
call php artisan config:cache --no-ansi >> "%OUT%" 2>&1 && (echo CONFIG OK >> "%OUT%") || (echo CONFIG FAILED >> "%OUT%")
call php artisan route:clear --no-ansi >nul 2>&1
call php artisan config:clear --no-ansi >nul 2>&1

echo   [5/8] Live-server readiness (preflight)...
echo. >> "%OUT%" & echo ===== 5. PREFLIGHT (failures here are EXPECTED on your PC - they list what the live server's settings must have) ===== >> "%OUT%"
call php artisan wb:preflight --no-ansi >> "%OUT%" 2>&1

echo   [6/8] Automated tests...
echo. >> "%OUT%" & echo ===== 6. TESTS ===== >> "%OUT%"
call php artisan test --colors=never >> "%OUT%" 2>&1
set "TESTS=%ERRORLEVEL%"

echo   [7/8] Security audit of packages...
echo. >> "%OUT%" & echo ===== 7. COMPOSER AUDIT ===== >> "%OUT%"
call composer audit --no-interaction >> "%OUT%" 2>&1
echo ===== NPM AUDIT ===== >> "%OUT%"
call npm audit --omit=dev >> "%OUT%" 2>&1

echo   [8/8] Front-end files...
echo. >> "%OUT%" & echo ===== 8. FRONT-END BUILD FILES ===== >> "%OUT%"
if exist "public\build\manifest.json" (echo BUILD OK - manifest present >> "%OUT%") else (echo BUILD FAILED - public\build\manifest.json missing >> "%OUT%")
if exist "public\hot" echo BUILD FAILED - public\hot exists, delete it before uploading >> "%OUT%"
if exist "public\storage" echo NOTE public\storage link present >> "%OUT%"

echo   ---------------------------------------------------------------
powershell -NoProfile -Command "Get-Content '%OUT%' | Select-String -Pattern 'Environment|Debug Mode|URL |Database|PENDING|VIEWS|ROUTES|CONFIG|BUILD|Preflight|Tests:|FAILED|No security|vulnerabilit' | Select-Object -First 40"
echo   ---------------------------------------------------------------
echo.
if "%TESTS%"=="0" (echo   Tests passed.) else (echo   Some tests FAILED.)
echo   Full report: predeploy-results.txt - tell Claude "check done" and it will read it.
echo.
pause
exit /b 0
