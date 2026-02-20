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

    //Verificar regresar
    public function generateWifi() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['ssid'])) {
            $this->sendJSONError('SSID es obligatorio', 400);
        }

        $ssid = $input['ssid'];
        $password = $input['password'] ?? '';
        $encryption = $input['encryption'] ?? 'WPA';
        $size = isset($input['size']) ? (int)$input['size'] : 300;
        $ecLevel = $input['error_correction'] ?? 'M';

        $wifiContent = $this->generator->buildWifiString($ssid, $password, $encryption);

        try {
            $imageBytes = $this->generator->generateBasic($wifiContent, $size, $ecLevel);
            header('Content-Type: image/png');
            http_response_code(200);
            echo $imageBytes;
            exit;
        } catch (Exception $e) {
            $this->sendJSONError('Error al generar QR de WiFi: ' . $e->getMessage(), 500);
        }
    }

    public function generateGeo() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['latitude']) || !isset($input['longitude'])) {
            $this->sendJSONError('Latitud y longitud son obligatoriasss', 400);
        }

        $lat = (float)$input['latitude'];
        $lng = (float)$input['longitude'];

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            $this->sendJSONError('Coordenadas mal: Latitud (-90 a 90), Longitud (-180 a 180)', 400);
        }

        $size = isset($input['size']) ? (int)$input['size'] : 300;
        $ecLevel = $input['error_correction'] ?? 'M';

        $geoContent = $this->generator->buildGeoString($lat, $lng);

        try {
            $imageBytes = $this->generator->generateBasic($geoContent, $size, $ecLevel);
            header('Content-Type: image/png');
            http_response_code(200);
            echo $imageBytes;
            exit;
        } catch (Exception $e) {
            $this->sendJSONError('Error al generar QR de Geo: ' . $e->getMessage(), 500);
        }
    }

    public function generateEmail() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['email'])) {
            $this->sendJSONError('emai;l obligatorio', 400);
        }
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $this->sendJSONError('El email no es válido', 400);
        }

        $email = $input['email'];
        $subject = $input['subject'] ?? '';
        $body = $input['body'] ?? '';
        $size = isset($input['size']) ? (int)$input['size'] : 300;
        $ecLevel = $input['error_correction'] ?? 'M';

        $emailContent = $this->generator->buildEmailString($email, $subject, $body);

        try {
            $imageBytes = $this->generator->generateBasic($emailContent, $size, $ecLevel);
            header('Content-Type: image/png');
            http_response_code(200);
            echo $imageBytes;
            exit;
        } catch (Exception $e) {
            $this->sendJSONError('Error al generar QR de Email: ' . $e->getMessage(), 500);
        }
    }
}

?>