#!/bin/sh
# Script para activar el estado (status=1) en la tabla statuses
# Uso:
#   ./activar_status.sh CODIGO
# Donde CODIGO es el código del dispensador (tabla codigo_dispensadors.codigo)

if [ -z "$1" ]; then
  echo "Uso: $0 CODIGO"
  exit 1
fi

CODIGO="$1"

# Ir a la carpeta donde está tu proyecto Laravel (ajusta si cambia la ruta en el servidor)
cd /home/u281546799/domains/laika.jademajesty.com/public_html || exit 1

# Ejecutar en tinker una actualización/creación del Status
/usr/bin/php artisan tinker --execute="\\App\\Models\\Status::updateOrCreate(['dispensador_id' => \\\App\\\Models\\\CodigoDispensador::where('codigo', '$CODIGO')->value('id')], ['status' => true]);" >> storage/logs/activar_status.log 2>&1

exit $? 
