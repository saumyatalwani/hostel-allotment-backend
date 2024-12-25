# About This Repository

Welcome to the **Hostel Booking System Backend** repository! This project is a comprehensive backend system for managing hostel room allocations, complaints, and user information. Built using PHP, it serves as a robust API layer for a dynamic web application. This project was developed as part of the **Introduction to Web Technology** course during the 5th semester.

The system is designed to facilitate smooth interactions between students and hostel administration by providing essential functionalities such as user registration, room selection, complaint management, and hostel assignment. With secure authentication using JWT, the backend ensures data integrity and restricted access to administrative functions.

This repository provides a solid foundation for understanding backend development concepts, including RESTful APIs, database integration, and role-based access control. By exploring this project, you will gain insights into building and deploying a practical web-based solution.

# Index

1.  Introduction
    - [Requirements](#requirements)
    - [Creation of `.env` File](#creation-of-env-file)
    - [Installation of Dependencies](#installation-of-dependencies)

2. API Documentation
    - General Information
        - [Authenticated Routes](#authenticated-routes)

    - User Management
        - [/api/register.php](#apiregisterphp)
        - [/api/login.php](#apiloginphp)

    -  Room Management
        - [/api/getAllRooms](#apigetallrooms)
        - [GET /api/rooms](#get-apirooms)
        - [POST /api/rooms](#post-apirooms)
        - [/api/SelectRoom](#apiselectroom)
        - [/api/getReserved](#apigetreserved)

    - Complaint Management
        - [GET /api/complaint](#get-apicomplaint)
        - [PUT /api/complaint](#put-apicomplaint)
        - [POST /api/complaint](#post-apicomplaint)

    - Hostel Management
        - [/api/getHostelBatch](#apigethostelbatch)
        - [GET /api/HostelBatch](#get-apihostelbatch)
        - [POST /api/HostelBatch](#post-apihostelbatch)
        - [/api/getHostelGender](#apigethostelgender)
        - [GET /api/HostelGender](#get-apihostelgender)
        - [POST /api/HostelGender](#post-apihostelgender)

    - User Administration
        - [/api/users](#apiusers)

    - Hostel ID Form
        - [/api/checkFilled](#apicheckfilled)
        - [GET /api/idForm](#get-apiidform)
        - [POST /api/idForm](#post-apiidform)


## Requirements

[XAMPP](https://www.apachefriends.org) 
[Composer](https://getcomposer.org/download/)

## Creation of `.env` File

The `.env` file in the project contains sensitive information such as database credentials and secret keys.
You should include the following variables in your env file:

```js
DB_HOST = ''
DB_NAME = ''
DB_USER = ''
DB_PASS = ''
SECRET_KEY = ""
```

Generate a secret key using `openssl rand -base64 64` in your terminal

## Installation of Dependencies

use `composer install` or `php composer.phar install` depending upon the way your system is setup

# API Documentation

## /api/register.php

- To register a new user.

- Compulsory to send a POST request with `content-type` set to `application/json` with body format:

```js
{
    "email":"...",
    "password":"...",
    "name":"...",
    "rollNo":"...",
    "gender":"..."
}
```

Return Codes : 

HTTP Response Code | Response
--- | ---
200 | Succesful registration
400 | missing any of the above specified three fields or if data is invalid
405 | not having POST
415 | not having json data in body

## /api/login.php

- To authenticate a new user.
- compulsory to send a POST request with `content-type` set to `application/json` with body format:

```js
{
    "email":"...",
    "password":"..."
}
```

Return Codes :

HTTP Response Code | Response
--- | ---
200 | Correct Credentials
400 | missing any of the above specified fields or if data is invalid
401 | not existing user or incorrect password
405 | not having POST
415 | not having json data in body


## Authenticated Routes

- All the Authenticated Routes need Bearer Token in Header

### Rooms

#### /api/getAllRooms

- To get all rooms in a hostel block
- Compulsorily GET request
- Required Parameter:
    block=""
- example request: ```/api/getAllRooms?block="..."```

Return Codes :

HTTP Response Code | Response
--- | ---
200 | Return All Rooms in a Block
405 | not having GET

#### GET /api/rooms

- Returns all rooms for admin panel in array form, need role set to admin in JWT Authorization Token

Return Codes :

HTTP Response Code | Response
--- | ---
200 | Return All Rooms
500 | Internal Server Error (PDO Exception)

#### POST /api/rooms

- add a room to database.
- `content-type` set to `application/json` with body format:

```js
{
    "room_no":...,
    "block_no":"...",
    "hostel_type":"..."
}
```

Return Codes :

HTTP Response Code | Response
--- | ---
200 | Successfully add room in database.
400 | Invalid JSON Data OR missing data OR Room No. is not an integer
415 | Not having json data in body.

#### /api/SelectRoom

- Select a room for a user.
- Compulsory PUT request with `content-type` set to `application/json` with body format:

```js
{
    "roomNo":...,
    "hostel":"...",
    "block":"...",
    "bed1":"...",
    "bed2":"...",
    "bed3":"..."
}
```
- bed1,bed2,bed3 are optional paramaters. However, should be included in request, should be left empty if not used. (USE ATLEAST ONE)

Return Codes :

HTTP Response Code | Response
--- | ---
200 | Succesful Room Selection.
400 | Invalid JSON Data OR missing data OR Room No. is not an integer or Selected Room mate hasn't uploaded Documents
405 | not having PUT
415 | Not having json data in body.
500 | Internal Server Error

#### /api/getReserved

- Checks if room is reserved for roll no. from authorization token.
- must be GET request Compulsory.
- returns room details if reserved room is present else returns null.

Return Codes :

HTTP Response Code | Response
--- | ---
200 | Succesful Room Selection.
405 | not having GET Request
500 | Internal Server Error (PDO Exception)


### Complaints

#### GET /api/complaint

- Gives an array of all complaints.
- Requires admin role authentication in JWT Token Authorization.

HTTP Response Code | Response
--- | ---
200 | Succesful Output
401 | Invalid Authentication. (i.e. no admin role)

#### PUT /api/complaint

- Updates complaint status to resolved.
- Requires admin role authentication in JWT Token Authorization.
- Body: 

```js
{
    "id":...
}
```

HTTP Response Code | Response
--- | ---
200 | Succesful Update.
400 | Invalid JSON Data or Missing Data.
401 | Invalid Authentication. (i.e. no admin role)
415 | Only JSON content is supported.


#### POST /api/complaint

- Create a new complaint.
- Body: 

```js
{
    "complaintType": "...",
    "description": "...",
    "phoneNo": "...",
    "email": "..."
}
```

HTTP Response Code | Response
--- | ---
200 | Succesful Complaint Creation.
400 | Invalid JSON Data OR Missing Data OR phoneNo is not an interger.
415 | Only JSON content is supported.
500 | Failure Creating Complaint (Internal Server Error)

### Hostel

#### /api/getHostelBatch

- GET Request
- Get Hostel from Batch & Degree
- Parameters:
    batch = integer ( e.g. 22 )
    degree = char (e.g. B )
- example : ```/api/getHostelBatch?batch=...&degree=...```

Return Codes :

HTTP Response Code | Response
--- | ---
200 | Success
400 | Missing Paramater or Batch is not an integer.

#### GET /api/HostelBatch

- Gives a list of mappings of batches & degrees to hostels.
- Requires admin role authentication in JWT Token Authorization.

HTTP Response Code | Response
--- | ---
200 | Succesful Output
401 | Invalid Authentication. (i.e. no admin role)
405 | Invalid Request Method

#### POST /api/HostelBatch

- Creates a batch, degree and hostel mapping
- Body:

```js
{
    "batch":...,
    "degree":"...",
    "hostel":"..."
}
```

HTTP Response Code | Response
--- | ---
200 | Succesful Output
400 | Invalid JSON Data OR Missing Data or batch is not an integer.
401 | Invalid Authentication. (i.e. no admin role)
405 | Invalid Request Method
415 | Invalid Content Type (JSON REQUIRED)

#### /api/getHostelGender

- GET Request
- returns all available hostels from a user
- takes email and gender from the JWT authentication token

Return Codes :

HTTP Response Code | Response
--- | ---
200 | Success
405 | Invalid Request Method

#### GET /api/HostelGender

- Gives a list of mappings of hostels and genders.
- Requires admin role authentication in JWT Token Authorization.

HTTP Response Code | Response
--- | ---
200 | Succesful Output
401 | Invalid Authentication. (i.e. no admin role)
405 | Invalid Request Method

#### POST /api/HostelGender

- Creates a gender and hostel mapping
- Requires admin role authentication in JWT Token Authorization.
- Body:

```js
{
    "hostel_type":"...",
    "block_no":"...",
    "gender":"..."
}
```


HTTP Response Code | Response
--- | ---
200 | Succesful Output
400 | Invalid JSON Data or Missing Data.
401 | Invalid Authentication. (i.e. no admin role)
405 | Invalid Request Method
415 | Invalid Content Type (JSON REQUIRED)

### Users

#### /api/users

- GET Request
- Gives a list of all users to the admin.
- Requires admin role authentication in JWT Token Authorization.

HTTP Response Code | Response
--- | ---
200 | Succesful Output
401 | Invalid Authentication. (i.e. no admin role)
405 | Invalid Request Method ( GET Required )


### Hostel ID Form

#### /api/checkFilled

- Checks if Hostel ID Form is Filled or Not from JWT Authorization Token
- Compulsorily GET request
- Return Message Format: 
```js
{
    "message":"true/false"
}
```

Return Codes :

HTTP Response Code | Response
--- | ---
200 | Return All Rooms in a Block
405 | not having GET

#### GET /api/idForm

- GET Request
- returns all users who have filled the hostel ID Form.
- Requires admin role authentication in JWT Token Authorization.

Return Codes :

HTTP Response Code | Response
--- | ---
200 | Success
401 | Invalid Authentication ( Admin Required )
405 | Invalid Request Method

#### POST /api/idForm

- Request to Fill Hostel ID Form.
- POST Request
- Compulsory to send a POST request with `content-type` set to `application/json` with body format:
```js
{
    "studentPhone": "...",
    "address": "...",
    "parentPhone": "...",
    "email": "..."
}
```


HTTP Response Code | Response
--- | ---
200 | Succesful Output
400 | Invalid JSON Data OR Missing Data OR Phone is not an integer.
401 | Invalid Authentication. (i.e. no admin role)
405 | Invalid Request Method
415 | Invalid Content Type (JSON REQUIRED)
500 | Failure filling data.