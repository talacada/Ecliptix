`docker compose up -d` to start the application, then open http://localhost:8000 in your browser.

`symfony serve -d` to start the server, then open http://localhost:8000 in your browser.

`php bin/console make:migration` to create a migration file, then `php bin/console doctrine:migrations:migrate` to run the migration and create the database schema.

In container `composer install` to install the dependencies.
