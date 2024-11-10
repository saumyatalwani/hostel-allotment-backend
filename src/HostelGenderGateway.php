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
}
