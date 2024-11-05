<?php

// Set CORS headers
header("Access-Control-Allow-Origin: *"); // Replace * with a specific origin if needed
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle OPTIONS preflight request

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('ALLOW: POST');
    echo "ALLOW post";
    exit();
}

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';


if ($contentType !== 'application/json') {
    http_response_code(415);
    echo json_encode(["message" => "Only JSON content is supported"]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid JSON data"]);
    exit();
}


if (!array_key_exists('email', $data) || !array_key_exists('password', $data)) {
    http_response_code(400);
    echo json_encode(["message" => "Missing login credentials"]);
    exit();
}

$user_gateway = new UserGateway($database);

$user = $user_gateway->getByEmail($data['email']);

if ($user === false) {
    http_response_code(401);
    echo json_encode(["message" => "User Not Found!"]);
    exit;
}

if (!password_verify($data['password'], $user['Password'])) {
    http_response_code(401);
    echo json_encode(["message" => "Invalid Password!"]);
    exit;
}


require __DIR__ . "/tokens.php";

$refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);

$refresh_token_gateway->create($refresh_token, $refresh_token_expiry);


