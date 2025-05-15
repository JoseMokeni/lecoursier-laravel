# Le Coursier Laravel App

## Pre-requisites

-   Docker Infrastructure running on your machine (see PFE-SERVICES repository)

## Installation

1. Clone the repository
2. Copy the `.env.example` file to `.env` and fill in the necessary environment variables
3. Run `docker compose up -d` to start the containers
4. Run `docker compose exec app composer install` to install the dependencies
5. Run `docker compose exec app php artisan key:generate` to generate the application key
6. Run `docker compose exec app php artisan migrate` to run the migrations
7. Run `docker compose exec app php artisan db:seed` to seed the database
8. Build the frontend assets with `docker compose exec app npm install && docker compose exec app npm run build`
