<?php
class DocumentGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function postDocs(string $rollNo,string $hostel, string $email,string $fee_receipt,string $passport_photo, string $allotment_letter): bool
    {
        try{
            $this->conn->beginTransaction();
            $sqlInsert = "INSERT INTO `documents` (`rollNo`, `hostel`, `email`, `fee_receipt`, `passport_photo`, `allotment_letter`) VALUES (:rollNo, :hostel, :email, :fee_receipt, :passport_photo, :allotment_letter)";

            $stmt = $this->conn->prepare($sqlInsert);
            $stmt->bindValue(':rollNo', $rollNo, PDO::PARAM_STR);
            $stmt->bindValue(':hostel', $hostel, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':fee_receipt', $fee_receipt, PDO::PARAM_LOB);
            $stmt->bindValue(':passport_photo', $passport_photo, PDO::PARAM_LOB);
            $stmt->bindValue(':allotment_letter', $allotment_letter, PDO::PARAM_LOB);

            $stmt->execute();

            $sqlUpdate = "UPDATE `users` SET `Status` = 'selectRoom' WHERE `Email` = :email";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->bindValue(':email', $email, PDO::PARAM_STR);
            $stmtUpdate->execute();

            $this->conn->commit();
            return $stmt->rowCount() > 0 && $stmtUpdate->rowCount() > 0;
        } catch(Exception $e){
            $this->conn->rollBack();
            echo $e;
            return false;
        }
        

       
    }

    public function getHostel(string $email):string{
        $sql = "SELECT * FROM documents where email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data['hostel'];

    }

    public function documentsVerified(string $rollNo): bool{
        $sql = "SELECT 1 FROM documents WHERE rollNo = :rollNo";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':rollNo', $rollNo, PDO::PARAM_STR);
        $stmt->execute();

        // Return true if a record is found, otherwise false
        return $stmt->rowCount() > 0;
    }
}
