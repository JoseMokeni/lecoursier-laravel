# Le Coursier Laravel App

## Pre-requisites

-   Docker
-   Related Services: Redis, MySQL, Mailhog

## Installation

1. Clone the repository
2. Run `cp .env.example .env` and update the environment variables
3. Run `docker compose up -d`
4. Run `docker compose exec app composer install`
5. Run `docker compose exec app php artisan key:generate`
6. Run `docker compose exec app php artisan migrate`
7. Run `docker compose exec app php artisan db:seed`
8. To make the app consider the APP_KEY, stop and start the stack again
    ```bash
    docker compose down
    docker compose up -d
    ```
