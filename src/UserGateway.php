<?php

class UserGateway
{

    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }
    public function getByID(string $id): array | false
    {
        $sql = "SELECT *
                FROM users
                WHERE Email = :id";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByEmail(string $email): array | false
    {
        $sql = 'SELECT * FROM users WHERE email = :email';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAllJoin(){
        $sql = "SELECT u.Email, u.Full_Name, u.rollNo, u.Status, u.gender, d.hostel, d.passport_photo, d.allotment_letter, d.fee_receipt, r.room_no, r.block_no FROM users u LEFT JOIN documents d ON u.Email = d.email LEFT JOIN room r ON r.bed1 = u.rollNo OR r.bed2 = u.rollNo OR r.bed3 = u.rollNo WHERE NOT u.Role='admin';";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
