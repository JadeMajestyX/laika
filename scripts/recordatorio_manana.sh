#!/bin/sh
# Script para ejecutar el comando de recordatorio de citas de la app
# Ajusta la ruta si tu proyecto está en otro lugar



# Ejecuta el comando artisan (usa `php` del PATH; si necesitas una ruta absoluta, reemplaza `php` por /usr/bin/php8.3 por ejemplo)
php artisan citas:recordatorio-manana 

exit $?
