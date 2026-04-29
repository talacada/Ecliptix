#!/bin/sh
set -e

# Pokud chybí složka vendor, automaticky nainstaluj závislosti (řeší start z prázdným diskem přes volume)
if [ ! -d "vendor" ]; then
    echo "Složka 'vendor' nenalezena. Spouštím 'composer install'..."
    composer install --no-interaction
fi

# Volání rodičovského entrypointu FrankenPHP / případně našeho procesu
exec "$@"
