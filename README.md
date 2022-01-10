# About laravel-authentication-with-passport

This project is a Laravel Passport Authentication api that validates a user's name, email address, phone number, and password and returns the users data and passport tokens.

It also has an email verification feature on the registration endpoint.

This project is hosted on a digitalocean droplet and is running in an Ubuntu/Nginx server.

### The following endpoints are available in the application

- `GET / `
- `POST /api/v1/register`
- `POST /api/v1/verify_email`
- `GET /api/v1/resend/{id}`
- `POST /api/v1/login`
- `GET /api/v1/profile`
- `POST /api/v1/update-profile`

## Installation

This project was built with the popular Laravel (PHP) framework
-- PHP version 8
-- Laravel version 8x

-- steps --

- clone the repository with `git clone https://github.com/deus-hub/laravel-authentication-with-passport.git`
- change directory into the project folder
- Run `composer install` on your cmd or terminal
- Copy `.env.example` file to `.env` on the root folder. 
- You can type `copy .env.example .env` if using command prompt Windows or `cp .env.example .env` if using shell terminal, linux or mac
- Open your `.env` file and change the database name `DB_DATABASE` to whatever you have, username `DB_USERNAME` and password `DB_PASSWORD` field according to your database setup.
- Set up a mail client for registeration OTP
- Run `php artisan key:generate`
- Run `php artisan migrate`
- Run `php artisan passport:install`


## Testing

After running the application, you can test the various endpoints provided using postman

### Postman collection

For quick setup on postman, here is a link to a postman collection containing all the endpoints
:https://www.getpostman.com/collections/859a8538f68203ecd486

Development server is accessible via
:http://137.184.154.42
