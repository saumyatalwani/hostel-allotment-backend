<?php

class HostelBatchController
{
    public function __construct(private HostelBatchGateway $gateway)
    {
    }

    public function processRequest(string $method,int $batch, string $degree): void
    {
        if ($method == "GET") {
            echo json_encode($this->gateway->getHostel($batch,$degree));
            exit;
        }else{
            $this->methodNotAllowed("GET");
            exit;
        }
    }

    public function getAll(){
        echo json_encode($this->gateway->fetchAll());
        exit;
    }

    public function add(int $batch, string $degree, string $hostel){
        echo json_encode($this->gateway->insert($batch,$degree,$hostel));
        exit;
    }

    private function methodNotAllowed(string $allowed_method): void
    {
        http_response_code(405);
        header("Allow: $allowed_method");
    }

}