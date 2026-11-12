#!/bin/sh
set -e

# Install dependencies automatically if the vendor directory is missing, such as when starting with an empty volume.
if [ ! -d "vendor" ]; then
    echo "The 'vendor' directory is missing. Running 'composer install'..."
    composer install --no-interaction
fi

# Run the FrankenPHP entrypoint or the supplied command.
exec "$@"
