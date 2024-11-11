<?php

class IdGateway
{

    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }
    
    public function insert(string $email, int $studentNo, int $parentNo, string $address){
        $sql="INSERT INTO `hostel_id` (`email`, `student_no`, `residential_address`, `parent_no`) VALUES (:email, :studentNo, :resAddr, :parentNo)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':studentNo', $studentNo, PDO::PARAM_INT);
        $stmt->bindValue(':resAddr', $address, PDO::PARAM_STR);
        $stmt->bindValue(':parentNo', $parentNo, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount()>0;

    }
    public function fetchAllJoin(){
        $sql = "SELECT 
            u.Full_Name,
            u.rollNo,
            r.room_no,
            r.block_no,
            r.hostel_type,
            h.student_no,
            h.residential_address,
            h.parent_no,
            d.passport_photo
        FROM 
            hostel_id h
        INNER JOIN 
            users u ON u.Email = h.email
        INNER JOIN 
            room r ON r.bed1 = u.rollNo OR r.bed2 = u.rollNo OR r.bed3 = u.rollNo
        INNER JOIN 
            documents d ON u.Email = d.email;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
