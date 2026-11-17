@echo off
setlocal
cd /d "%~dp0"
set "PHP_BIN=%~dp0php\php.exe"
set "PHP_INI=%~dp0php\php.ini"
if not exist "%PHP_BIN%" (
  echo Falta php\php.exe. Copie la carpeta php del material de la practica.
  pause
  exit /b 1
)
if not exist "%PHP_INI%" (
  echo Falta php\php.ini. Copie la carpeta php completa del material de la practica.
  pause
  exit /b 1
)
start "" "http://127.0.0.1:8080"
"%PHP_BIN%" -c "%PHP_INI%" -S 127.0.0.1:8080
pause

