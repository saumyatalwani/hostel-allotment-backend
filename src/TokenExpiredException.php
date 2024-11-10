<?php

class TokenExpiredException extends Exception
{
    public function __construct()
    {
        // Call the parent constructor with a custom message
        parent::__construct("Token Expired, Login Again");
    }
}