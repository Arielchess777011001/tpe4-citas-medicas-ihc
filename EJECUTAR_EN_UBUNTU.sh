#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
if ! php -m | grep -qi '^pdo_sqlite$'; then
  echo 'Falta la extensión pdo_sqlite de PHP.' >&2
  exit 1
fi
echo 'Sistema disponible en http://127.0.0.1:8080'
php -S 127.0.0.1:8080 -t .

