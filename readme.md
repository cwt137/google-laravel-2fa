# Laravel Google Authenticator Example

This is the completed example code that shows how implement two-factor authentication in Laravel using Google Authenticator

## Install Instructions

* Clone repository

    ```bash
    git clone https://github.com/cwt137/google-laravel-2fa Project
    ```

    ```bash
    cd Project
    ```


* Install Dependencies and Setup Database

    ```bash
    composer install
    php -r "copy('.env.example', '.env');"
    php artisan key:generate
    php artisan migrate
    ```

## Read The Article

To learn more about this code, read the article:

