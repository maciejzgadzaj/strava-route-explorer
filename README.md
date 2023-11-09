# Strava Route Explorer

[Strava Route Explorer](https://stravaroutes.com/) is a social site for [Strava](https://www.strava.com/) users to share their routes with the community, and benefit for other atletes' routes.

## Wiki

- [About](https://github.com/maciejzgadzaj/strava-route-explorer/wiki/About)
- [Searching for routes](https://github.com/maciejzgadzaj/strava-route-explorer/wiki/Search-help)
- [Frequently Asked Questions](https://github.com/maciejzgadzaj/strava-route-explorer/wiki/FAQ)
- [For developers](https://github.com/maciejzgadzaj/strava-route-explorer/wiki/Developers)
- [Credits](https://github.com/maciejzgadzaj/strava-route-explorer/wiki/Credits)

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
