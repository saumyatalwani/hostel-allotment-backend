<?php

$payload = [
    "sub" => $user["Email"],
    "name" => $user["Full_Name"],
    "status" => $user["Status"],
    "email"=> $user["Email"],
    "exp" => time() + 20
];

$JwtController = new Jwt($_ENV["SECRET_KEY"]);

$access_token = $JwtController->encode($payload);

$refresh_token_expiry = time() + 432000;

$refresh_token = $JwtController->encode([
    "sub" => $user["Email"],
    "exp" => $refresh_token_expiry
]);

echo json_encode([
    "access_token" => $access_token,
    "refresh_token" => $refresh_token
]);