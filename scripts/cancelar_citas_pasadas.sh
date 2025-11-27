#!/bin/sh
# Script para cancelar citas pasadas no atendidas

# Ir a la carpeta donde está tu proyecto Laravel
cd /home/u281546799/domains/laika.jademajesty.com/public_html || exit 1

# Ejecutar el comando artisan que cancela las citas pasadas
# Usa la ruta absoluta a php si es necesario (/usr/bin/php)
/usr/bin/php artisan citas:cancelar-pasadas >> storage/logs/cancelar_citas_pasadas.log 2>&1

exit $?
