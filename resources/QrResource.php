<?php
require_once __DIR__ . '/../models/QrGenerator.php';

class QrResource{
    private $generator;

    public function __construct() {
        $this->generator = new QrGenerator();
    }

    private function sendJSONError($message, $statusCode = 400) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }

    public function generateTextOrUrl(){
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['data']) || empty($input['data'])) {
            $this->sendJSONError('Data es obligatorio');
        }

        $size = isset($input['size']) ? (int)$input['size'] : 300;
        $errorCorrectionLevel = $input['error_correction_level'] ?? 'M';
        if($size < 100 || $size > 1000) {
            $this->sendJSONError('El tamanio entre 100 y 1000 pixeles');
        }

        try{
            $qrData = $this->generator->generateBasic($input['data'], $size, $errorCorrectionLevel);
            header('Content-Type: image/png');
            http_response_code(200);
            echo $qrData;
            exit;
        } catch (Exception $e) {
            $this->sendJSONError('Error al generar el QR: ' . $e->getMessage(), 500);
        }
    }
}

?>