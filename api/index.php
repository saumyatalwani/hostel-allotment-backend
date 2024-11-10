<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

header("Access-Control-Allow-Origin: *"); // Replace * with a specific origin if needed
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$parts = explode("/", $path);


$resource = $parts[3];

$user = new UserGateway($database);
$JwtCtrl = new Jwt($_ENV["SECRET_KEY"]);
$auth = new Auth($user, $JwtCtrl);

if (!$auth->authenticateJWTToken()) {
    exit;
}

switch ($resource){

    case "getAllRooms":
        $email=$payloadData['email'];
        $gateway = new DocumentGateway($database);
        $hostel = $gateway->getHostel($email);
        $block = isset($_GET['block']) ? trim($_GET['block']) : '';
        $gateway = new RoomGateway($database);
        $controller = new RoomController($gateway);
        $controller->processRequest($_SERVER['REQUEST_METHOD'],$hostel,$block);
        break;
    
    case "SelectRoom":
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            header('ALLOW: PUT');
            echo "ALLOW put";
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

        if (!array_key_exists('roomNo', $data) || !array_key_exists('hostel', $data) || !array_key_exists('block', $data) || !array_key_exists('bed1', $data) || !array_key_exists('bed2', $data) || !array_key_exists('bed3', $data)  ){
            http_response_code(400);
            echo json_encode(["message" => "Missing data"]);
            exit();
        }

        $rollNo=$payloadData['rollNo'];
        $email=$payloadData['email'];
        $gateway = new DocumentGateway($database);

        $status1 = true;
        $beds = ['bed1', 'bed2', 'bed3'];

        foreach ($beds as $bed) {
            if ($data[$bed] === $rollNo) {
                continue;
            }
            
            if (!empty($data[$bed])) {
                if (!$gateway->documentsVerified($data[$bed])) {
                    $status1 = false;
                    $message="Not Allowed! ".$data[$bed]." hasn't uploaded Documents.";
                    http_response_code(400);
                    echo json_encode(["message"=>$message]);
                    exit;
                }
            }
        }


        $gateway = new RoomGateway($database);
        $controller = new RoomController($gateway);
        $result=$controller->processRequest($_SERVER['REQUEST_METHOD'],$data['hostel'],$data['block'],$data['roomNo'],$data['bed1'],$data['bed2'],$data['bed3'],$email);
        if($result==true){
            $user_gateway = new UserGateway($database);
            $user = $user_gateway->getByEmail($email);

            require __DIR__ . "/tokens.php";

            $refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);

            $refresh_token_gateway->create($refresh_token, $refresh_token_expiry);
        } else{
            http_response_code(500);
            //echo json_encode(["message"=>"Internal Server Error"]);
        }
        break;
    
    case "getReserved":
        $rollNo='r'.$payloadData['rollNo'];
        $gateway = new RoomGateway($database);
        $controller = new RoomController($gateway);
        $controller->checkReserved($_SERVER['REQUEST_METHOD'],$rollNo);
        break;
    
    case "getHostelBatch":
        $batch = isset($_GET['batch']) ? trim($_GET['batch']) : '';
        $degree = isset($_GET['degree']) ? trim($_GET['degree']) : '';

        // If batch or degree are missing, return a 400 Bad Request response
        if (empty($batch) || empty($degree)) {
            http_response_code(400);
            echo json_encode(["message" => "Missing required query parameters"]);
            exit();
        }

        if (!filter_var($batch, FILTER_VALIDATE_INT)) {
            http_response_code(400);
            echo json_encode(["message" => "Batch must be an integer"]);
            exit();
        }

        $number = intval($batch);

        $gateway = new HostelBatchGateway($database);
        $controller = new HostelBatchController($gateway);
        $controller->processRequest($_SERVER['REQUEST_METHOD'],$number,$degree);
        break;
    
    case "getHostelGender":
        if($_SERVER['REQUEST_METHOD']=='GET'){
        $gender=$payloadData['gender'];
        $email=$payloadData['email'];

        $gateway = new DocumentGateway($database);
        $hostel = $gateway->getHostel($email);

        $gateway2 = new HostelGenderGateway($database);
        $blocks = $gateway2->getBlocks($gender,$hostel);
        echo json_encode($blocks);
        } else{
            http_response_code(405);
            header('ALLOW: GET');
            echo "ALLOW GET";
            exit();
        }
        /*
        $gateway = new HostelBatchGateway($database);
        $controller = new HostelBatchController($gateway);
        $controller->processRequest($_SERVER['REQUEST_METHOD'],$number,$degree);*/
        break;

    
    
    case "uploadDocs":
        
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

        if (!array_key_exists('email', $data) || !array_key_exists('hostel', $data) || !array_key_exists('rollno', $data) || !array_key_exists('fee_receipt', $data) || !array_key_exists('passport_photo', $data) || !array_key_exists('allotment_letter', $data)  ){
            http_response_code(400);
            echo json_encode(["message" => "Missing data"]);
            exit();
        }
        
        $gateway = new DocumentGateway($database);
        $result = $gateway->postDocs($data['rollno'],$data['hostel'],$data['email'],$data['fee_receipt'],$data['passport_photo'],$data['allotment_letter']);

        if($result==true){
            $user_gateway = new UserGateway($database);
            $user = $user_gateway->getByEmail($data['email']);

            require __DIR__ . "/tokens.php";

            $refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);

            $refresh_token_gateway->create($refresh_token, $refresh_token_expiry);

        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failure in uploading"]);
        }
        
        break;


    default:
        http_response_code(404);
        exit;
}