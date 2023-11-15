# weather-portal

Weather information dashboard built with Symfony 6.3

## Requirements

Before you get started, ensure you have the following software installed on your system:

- PHP 8.0 or higher
- Symfony 6
- Composer (PHP package manager)

## Installation

Clone the repository to your local machine:

   git clone https://github.com/kdsdeva/weather-portal.git
   
## Install project dependencies using Composer:

    composer install
    
## Install yarn:

    yarn install

## Create the database schema and Configure .env:

Configure your Symfony environment. You'll need to set up your database connection in .env file.

    php bin/console doctrine:schema:update --force

Configure your OPEN_CAGE_API_KEY in .env to get the Geocode.
Configure your WEATHER_API_KEY in .env to get the Weather information.

## Usage

- Register an account and log in, you will get to the Home Page
- If you reach the home page allow location permission for current location weather.
- To add a new city click "Add City".
- To set temperature alert click "set temperature alert".
- You can see an alert notification every 5 min on the Notification page(to get to the page click the Bell icon).
