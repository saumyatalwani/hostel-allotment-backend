<?php
class HostelGenderGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getBlocks(string $gender,string $hostel): array
    {
        $sql = "SELECT * FROM blockGender where gender = :gender and hostel_type = :hostel";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':gender', $gender, PDO::PARAM_STR);
        $stmt->bindValue(':hostel', $hostel, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchAll(){
        $sql = "SELECT * FROM blockGender";

        $stmt = $this->conn->prepare($sql);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function insert(string $gender,string $hostel, string $block): array
    {
        $sql = "INSERT INTO blockGender (block_no, hostel_type, gender) values (:block, :hostel, :gender);";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':gender', $gender, PDO::PARAM_STR);
        $stmt->bindValue(':hostel', $hostel, PDO::PARAM_STR);
        $stmt->bindValue(':block', $block, PDO::PARAM_STR);
        $stmt->execute();

        return $this->fetchAll();
    }
}
