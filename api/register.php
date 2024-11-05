<?php

//require __DIR__ . "/vendor/autoload.php";
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
$conn = $database->getConnection();
$sql = "INSERT INTO users (Full_Name, Email, Password, Status, Role) VALUES (:name, :email, :password_hash,:status,:role)";

$stmt = $conn->prepare($sql);
$password_hash = password_hash($data["password"], PASSWORD_DEFAULT);


if ($data === null) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid JSON data"]);
    exit();
}

if (!array_key_exists('email', $data) || !array_key_exists('password', $data) || !array_key_exists('name', $data)) {
    http_response_code(400);
    echo json_encode(["message" => "Missing credentials"]);
    exit();
}

$stmt->bindValue(":name", $data["name"], PDO::PARAM_STR);
$stmt->bindValue(":email", $data["email"], PDO::PARAM_STR);
$stmt->bindValue(":password_hash", $password_hash, PDO::PARAM_STR);
$stmt->bindValue(":status", "docVerify", PDO::PARAM_STR);
$stmt->bindValue(":role", "user", PDO::PARAM_STR);
$stmt->execute();
http_response_code(200);
echo json_encode([["message" => "user registered succesfully"]]);
exit;