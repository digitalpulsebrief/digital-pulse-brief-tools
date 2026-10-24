@echo off
setlocal
cd /d "%~dp0"
title Digital Pulse Brief - Wi-Fi Diagnostics
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0WiFi-Diagnostics.ps1" -SaveReport
set "EXITCODE=%ERRORLEVEL%"
echo.
if "%EXITCODE%"=="0" echo Core checks found no obvious problem.
if "%EXITCODE%"=="1" echo Review the warnings above and the local report.
if "%EXITCODE%"=="2" echo The diagnostic could not finish; review the error above.
echo Press any key to close.
pause >nul
exit /b %EXITCODE%
