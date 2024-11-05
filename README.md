
# About This Repository

## Requirements

[XAMPP](https://www.apachefriends.org) 
[Composer](https://getcomposer.org/download/)

## Creation of `.env` File

The `.env` file in the project contains sensitive information such as database credentials and secret keys.
You should include the following variables in your env file:

`DB_HOST = ''`
`DB_NAME = ''`
`DB_USER = ''`
`DB_PASS = ''`
`SECRET_KEY = ""`

Generate a secret key using `openssl rand -base64 64` in your terminal

## Installation of Dependencies

use `composer install` or `php composer.phar install` depending upon the way your system is setup

# API Documentation

### /api/register.php
compulsory to send a POST request with `content-type` set to `application/json` with body format:
`{
    "email":"...",
    "password":"...",
    "name":"..."
}`

returns 200 on Succesful registration
        400 on missing any of the above specified three fields or if data is invalid
        405 on not having POST
        415 on not having json data in body

### /api/login.php
compulsory to send a POST request with `content-type` set to `application/json` with body format:
`{
    "email":"...",
    "password":"...",
}`

returns 200 on Correct Credentials
        400 on missing any of the above specified fields or if data is invalid
        401 on not existing user or incorrect password
        405 on not having POST
        415 on not having json data in body
