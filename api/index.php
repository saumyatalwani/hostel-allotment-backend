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
                }else{
                    $data[$bed]='r'.$data[$bed];
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
    
    case "complaint":

        $complaintGateway=new complaintGateway($database);

        switch($_SERVER['REQUEST_METHOD']){
            case "GET":
                $role=$payloadData['role'];

                if($role!='admin'){
                    http_response_code(401);
                    echo json_encode(["message"=>"Unauthorized"]);
                }

                echo json_encode($complaintGateway->fetchAllJoin());

                break;
            case "PUT":
                $role=$payloadData['role'];
    
                if($role!='admin'){
                    http_response_code(401);
                    echo json_encode(["message"=>"Unauthorized"]);
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

                    if (!array_key_exists('id', $data)){
                        http_response_code(400);
                        echo json_encode(["message" => "Missing data"]);
                        exit();
                    }

                    echo json_encode($complaintGateway->mark($data['id']));
                    
                break;
            case "POST":
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

                    if (!array_key_exists('email', $data) || !array_key_exists('complaintType', $data) || !array_key_exists('description', $data) || !array_key_exists('phoneNo', $data)){
                        http_response_code(400);
                        echo json_encode(["message" => "Missing data"]);
                        exit();
                    }

                    if (!filter_var($data['phoneNo'], FILTER_VALIDATE_INT)) {
                        http_response_code(400);
                        echo json_encode(["message" => "Phone must be an integer"]);
                        exit();
                    }
                    
                    $number = intval($data['phoneNo']);

                    $result = $complaintGateway->insert($data['email'],$data['complaintType'],$data['description'],$number);

                    if ($result==true){
                        echo json_encode(["message"=>"Success"]);
                    }else{
                        http_response_code(500);
                        echo json_encode(["message"=>"Failure"]);
                    }



                break;

            default:
                break;
        }
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
    
        case "HostelBatch":

            $role=$payloadData['role'];

            if($role!='admin'){
                http_response_code(401);
                echo json_encode(["message"=>"Unauthorized"]);
            }

            $gateway = new HostelBatchGateway($database);
            $controller = new HostelBatchController($gateway);

            switch($_SERVER['REQUEST_METHOD']){
                case "GET":
                    $controller->getAll();
                    break;
                case "POST":
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

                    if (!array_key_exists('batch', $data) || !array_key_exists('hostel', $data) || !array_key_exists('degree', $data)){
                        http_response_code(400);
                        echo json_encode(["message" => "Missing data"]);
                        exit();
                    }

                    if (!filter_var($data['batch'], FILTER_VALIDATE_INT)) {
                        http_response_code(400);
                        echo json_encode(["message" => "Batch must be an integer"]);
                        exit();
                    }
                    
                    $number = intval($data['batch']);

                    $controller->add($number,$data['degree'],$data['hostel']);
                    break;
                default:
                    http_response_code(405);

            }
            
            
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
    case "HostelGender":

            $role=$payloadData['role'];

            if($role!='admin'){
                http_response_code(401);
                echo json_encode(["message"=>"Unauthorized"]);
            }

            $gateway = new HostelGenderGateway($database);

            switch($_SERVER['REQUEST_METHOD']){
                case "GET":
                    echo json_encode($gateway->fetchAll());
                    break;
                case "POST":
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

                    if (!array_key_exists('block_no', $data) || !array_key_exists('hostel_type', $data) || !array_key_exists('gender', $data)){
                        http_response_code(400);
                        echo json_encode(["message" => "Missing data"]);
                        exit();
                    }

                    echo json_encode($gateway->insert($data['gender'],$data['hostel_type'],$data['block_no']));
                    break;
                default:
                    http_response_code(405);

            }
            
            
            break;

        case "users":

                $role=$payloadData['role'];
    
                if($role!='admin'){
                    http_response_code(401);
                    echo json_encode(["message"=>"Unauthorized"]);
                }
    
                $gateway = new UserGateway($database);

    
                switch($_SERVER['REQUEST_METHOD']){
                    case "GET":
                        echo json_encode($gateway->fetchAllJoin());
                        break;
                    default:
                        http_response_code(405);
                
                    }
                break;
        case "idForm":

            $gateway = new IdGateway($database);
    
        
            switch($_SERVER['REQUEST_METHOD']){
                case "GET":
                    $role=$payloadData['role'];
        
                    if($role!='admin'){
                        http_response_code(401);
                        echo json_encode(["message"=>"Unauthorized"]);
                    }
                    echo json_encode($gateway->fetchAllJoin());
                    break;
                case "POST":
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

                    if (!array_key_exists('email', $data) || !array_key_exists('address', $data) || !array_key_exists('studentPhone', $data) || !array_key_exists('parentPhone', $data)){
                        http_response_code(400);
                        echo json_encode(["message" => "Missing data"]);
                        exit();
                    }

                    if (!filter_var($data['studentPhone'], FILTER_VALIDATE_INT) || !filter_var($data['parentPhone'], FILTER_VALIDATE_INT)) {
                        http_response_code(400);
                        echo json_encode(["message" => "phone must be an integer"]);
                        exit();
                    }
            
                    $number1 = intval($data['studentPhone']);
                    $number2 = intval($data['parentPhone']);

                    $result=$gateway ->insert($data['email'],$number1,$number2,$data['address']);

                    if($result==true){
                        echo json_encode(["message"=>"success"]);
                    }else{
                        http_response_code(500);
                        echo json_encode(["message"=>"Failure"]);
                    }


                    break;
                default:
                    http_response_code(405);
                    
            }
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