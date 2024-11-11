<?php
class RoomGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getRooms($hostel,$block): array
    {
        $sql = "SELECT * FROM room where hostel_type = :hostel and block_no = :block";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':hostel', $hostel, PDO::PARAM_STR);
        $stmt->bindValue(':block', $block, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function updateRoom($hostel, $block, $roomNo,$email,$bed1 = null, $bed2 = null, $bed3 = null): bool
{
    try {
        // Begin the transaction
        $this->conn->beginTransaction();

        // Update user status to 'RoomSelected'
        $sql2 = "UPDATE `users` SET `Status` = 'RoomSelected' WHERE `Email` = :email";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt2->execute();
        
        // Initialize the SQL query for updating the room
        $sql = "UPDATE `room` SET ";
        $params = [];

        // Conditionally add each bed column to the query if it has a value
        if ($bed1 !== null && $bed1 !== '') {
            $sql .= "`bed1` = :bed1, ";
            $params[':bed1'] = $bed1;
        }
        if ($bed2 !== null && $bed2 !== '') {
            $sql .= "`bed2` = :bed2, ";
            $params[':bed2'] = $bed2;
        }
        if ($bed3 !== null && $bed3 !== '') {
            $sql .= "`bed3` = :bed3, ";
            $params[':bed3'] = $bed3;
        }

        // Remove the trailing comma and space
        $sql = rtrim($sql, ', ') . " WHERE hostel_type = :hostel AND block_no = :block AND room_no = :roomNo";

        // Add mandatory parameters
        $params[':hostel'] = $hostel;
        $params[':block'] = $block;
        $params[':roomNo'] = $roomNo;

        // Prepare and execute the room update query
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        
        // Commit the transaction if both queries were successful
        $this->conn->commit();

        // Return true if any row was affected in the room update
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        // Rollback the transaction if an error occurs
        $this->conn->rollBack();
        
        // Log the error message for debugging (optional)
        echo json_encode(["message"=>"Error in transaction"]);
        
        // Return false in case of failure
        return false;
    }
}

public function getReserved($rollNo) {
    try {
        $sql = "SELECT * FROM room WHERE bed1 = :rollNo1 OR bed2 = :rollNo2 OR bed3 = :rollNo3";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':rollNo1', $rollNo, PDO::PARAM_STR);
        $stmt->bindValue(':rollNo2', $rollNo, PDO::PARAM_STR);
        $stmt->bindValue(':rollNo3', $rollNo, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result;
        } else {
            return null;
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message"=>$e->getMessage()]);
        return false;
    }
}

public function getAllRooms(){
    try {
        $sql = "SELECT * FROM room order by block_no,room_no;";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $result;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message"=>$e->getMessage()]);
        return [];
    }
}

public function insert($room_no,$block_no,$hostel_type){
    try {
        $sql = "INSERT INTO room (room_no, block_no, hostel_type) VALUES (:room_no, :block_no, :hostel_type);";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':room_no', $room_no, PDO::PARAM_INT);
        $stmt->bindValue(':block_no', $block_no, PDO::PARAM_STR);
        $stmt->bindValue(':hostel_type', $hostel_type, PDO::PARAM_STR);
        $stmt->execute();
        return $this->getAllRooms();
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message"=>$e->getMessage()]);
        //return false;
    }
}



}
