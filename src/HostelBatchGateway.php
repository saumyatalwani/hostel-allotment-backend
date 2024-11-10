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
}
