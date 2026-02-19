<?php
require_once __DIR__ . '/../config/db.php';

class TestDB {
    
    public function checkConnection() {
        $database = new Database();
        $conn = $database->getConnection();

        header('Content-Type: application/json');
        
        if ($conn) {
            http_response_code(200);
            echo json_encode([
                "status" => "success", 
                "message" => "SI"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error", 
                "message" => "NO"
            ]);
        }
    }
}