<?php
class HostelBatchGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getHostel(int $batch,string $degree): array
    {
        $sql = "SELECT * FROM batchHostel where batch = :batch and degree = :degree";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':batch', $batch, PDO::PARAM_INT);
        $stmt->bindValue(':degree', $degree, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAll(){
        $sql = "SELECT * FROM batchHostel";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(int $batch, string $degree, string $hostel){
        $sql = "INSERT INTO batchHostel (batch, degree, hostel) values (:batch, :degree, :hostel)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':batch', $batch, PDO::PARAM_INT);
        $stmt->bindValue(':degree', $degree, PDO::PARAM_STR);
        $stmt->bindValue(':hostel', $hostel, PDO::PARAM_STR);
        $stmt->execute();

        $data = $this->fetchAll();

        return $data;
        
    }
}
