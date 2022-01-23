# Strava Route Explorer

## Description

## Installation

```
git clone git@github.com:maciejzgadzaj/strava-route-explorer.git strava-route-explorer
cd strava-route-explorer

# Resolve and install dependencies:
composer install

# Setup the .env file and configure database, Strava and MapQuest API access details:
cp .env.dist .env
vim .env

# Create database and schema:
php bin/console doctrine:database:create
php bin/console doctrine:database:create --connection=geonames
php bin/console doctrine:schema:update --force
```
