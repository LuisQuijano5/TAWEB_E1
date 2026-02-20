<?php
require_once __DIR__ . '/../models/GenPassword.php';

class PasswordResource {
    
    private function sendJSON($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // CASO 1
    public function generateSingle() {
        try {
            $length = isset($_GET['length']) ? (int)$_GET['length'] : 16;
            
            $opts = [
                'upper' => isset($_GET['includeUppercase']) ? filter_var($_GET['includeUppercase'], FILTER_VALIDATE_BOOLEAN) : true,
                'lower' => isset($_GET['includeLowercase']) ? filter_var($_GET['includeLowercase'], FILTER_VALIDATE_BOOLEAN) : true,
                'digits' => isset($_GET['includeNumbers']) ? filter_var($_GET['includeNumbers'], FILTER_VALIDATE_BOOLEAN) : true,
                'symbols' => isset($_GET['includeSymbols']) ? filter_var($_GET['includeSymbols'], FILTER_VALIDATE_BOOLEAN) : true,
                'pattern' => $_GET['pattern'] ?? ''
            ];

            $password = PasswordGenerator::generate_password($length, $opts);
            $this->sendJSON(['password' => $password]);
            
        } catch (InvalidArgumentException $e) {
            $this->sendJSON(['error' => $e->getMessage()], 400);
        }
    }

    // CASO 2
    public function generateMultiple() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        try {
            $count = $input['count'] ?? 5;
            $length = $input['length'] ?? 16;
            
            $opts = [];
            
            if (isset($input['includeUppercase'])) $opts['upper'] = (bool)$input['includeUppercase'];
            if (isset($input['includeLowercase'])) $opts['lower'] = (bool)$input['includeLowercase'];
            if (isset($input['includeNumbers'])) $opts['digits'] = (bool)$input['includeNumbers'];
            if (isset($input['includeSymbols'])) $opts['symbols'] = (bool)$input['includeSymbols'];
            if (isset($input['excludeAmbiguous'])) $opts['avoid_ambiguous'] = (bool)$input['excludeAmbiguous'];
            if (isset($input['pattern'])) $opts['pattern'] = (string)$input['pattern']; 

            $passwords = PasswordGenerator::generate_passwords($count, $length, $opts);
            
            $this->sendJSON(['passwords' => $passwords]);

        } catch (InvalidArgumentException $e) {
            $this->sendJSON(['error' => $e->getMessage()], 400);
        }
    }

    // CASO 3
    public function validate() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['password']) || !isset($input['requirements'])) {
            $this->sendJSON(['error' => 'Faltan datos requeridos'], 400);
        }

        $result = PasswordGenerator::validate_password($input['password'], $input['requirements']);
        $this->sendJSON($result);
    }
}
?>