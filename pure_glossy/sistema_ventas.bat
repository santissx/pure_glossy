@echo off
title Sistema de Ventas
:: Se posiciona automáticamente en la carpeta donde está este archivo
cd /d "%~dp0"

echo Iniciando Sistema...
if not exist .env (
    echo Creando archivo de configuracion .env...
    copy .env.example .env
    echo Generando clave de cifrado unica...
    php artisan key:generate
)

start http://127.0.0.1:8000
php artisan serve
pause