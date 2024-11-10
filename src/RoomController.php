<?php

class RoomController
{
    public function __construct(private RoomGateway $gateway)
    {
    }

    public function processRequest(string $method,string $hostel, string $block, int $roomNo=0,string $bed1='',string $bed2 ='', string $bed3='', string $email=''): bool
    {
        

        switch($method){
            case "GET":
                echo json_encode($this->gateway->getRooms($hostel,$block));
                return true;
                //exit;
            case "PUT":
                $success = $this->gateway->updateRoom($hostel,$block,$roomNo,$email,$bed1,$bed2,$bed3);
                if($success==true){
                    //echo json_encode(["message"=>"Succeess"]);
                    return true;
                }else{
                    echo json_encode(["message"=>"failure"]);
                    return false;
                }
                

            default:
                $this->methodNotAllowed("GET,PUT");
                return false;
                //exit;
        }
    }

    public function checkReserved(string $method, string $rollNo){
        if($method=="GET"){
            echo json_encode($this->gateway->getReserved($rollNo));
            exit;
        }else{
            $this->methodNotAllowed("GET");
            exit;
        }
    }

    private function methodNotAllowed(string $allowed_method): void
    {
        http_response_code(405);
        header("Allow: $allowed_method");
    }

}