@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title WestBridge Technologies - automated tests

echo.
echo   Running the automated tests. This takes a minute or two.
echo   They use a throwaway in-memory database - your real data is not touched.
echo.

if not exist "westbridge\artisan" (
    echo   [X] The site is not installed yet. Run RUN-ME.bat first.
    pause
    exit /b 1
)

cd westbridge
call php artisan config:clear >nul 2>&1
call php artisan test --colors=never > "%~dp0test-results.txt" 2>&1
set "RESULT=%ERRORLEVEL%"

echo   Checking packages for known security problems...
echo. >> "%~dp0test-results.txt"
echo ===== composer audit ===== >> "%~dp0test-results.txt"
call composer audit --no-interaction >> "%~dp0test-results.txt" 2>&1
set "AUDIT=%ERRORLEVEL%"
echo ===== npm audit ===== >> "%~dp0test-results.txt"
call npm audit --omit=dev >> "%~dp0test-results.txt" 2>&1
if not "%AUDIT%"=="0" echo   [!] composer audit reported problems - see test-results.txt

echo   ---------------------------------------------------------------
powershell -NoProfile -Command "Get-Content '%~dp0test-results.txt' | Select-String -Pattern 'Tests:|FAILED|FAIL |No security|advisor|vulnerabilit' | Select-Object -Last 25"
echo   ---------------------------------------------------------------
echo.
if "%RESULT%"=="0" (
    echo   ALL TESTS PASSED.
) else (
    echo   Some tests FAILED. The full report is in test-results.txt
    echo   next to this file - tell Claude and it will read it.
)
echo.
pause
exit /b %RESULT%
