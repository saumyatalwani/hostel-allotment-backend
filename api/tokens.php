<?php

$payload = [
    "sub" => $user["Email"],
    "name" => $user["Full_Name"],
    "status" => $user["Status"],
    "email"=> $user["Email"],
    "rollNo"=> $user["rollNo"],
    "gender" => $user["gender"],
    "role" => $user["Role"],
    "exp" => time() + 60000
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