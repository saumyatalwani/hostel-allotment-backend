<?php

class complaintGateway
{

    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }
    
    public function insert(string $email, string $complaintT, string $desc, int $phone){
        $sql="INSERT INTO `complaints` (`email`, `complaintType`, `description`, `phoneNo`,`status`) VALUES (:email, :compT, :desc, :phone,'Pending')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':compT', $complaintT, PDO::PARAM_STR);
        $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
        $stmt->bindValue(':phone', $phone, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount()>0;

    }
    public function fetchAllJoin(){
        $sql = "SELECT 
                comp.complaintID,
                usr.Full_Name,
                comp.phoneNo,
                rm.block_no,
                rm.room_no,
                comp.complaintType,
                comp.description,
                comp.status
            FROM 
                complaints comp
            INNER JOIN 
                users usr ON usr.Email = comp.email
            INNER JOIN 
                room rm ON usr.rollNo IN (rm.bed1, rm.bed2, rm.bed3);";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mark($id){
        $sql = "UPDATE `complaints` SET `status` = 'Resolved' WHERE `complaints`.`complaintID` = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $this->fetchAllJoin();
    }
}
