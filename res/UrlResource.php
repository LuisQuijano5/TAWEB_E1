<?php
require_once __DIR__ . '/../models/UrlShortener.php';

class UrlController {
    private $model;

    public function __construct() {
        $this->model = new UrlShortener();
    }

    private function sendJSON($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function shorten() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['original_url']) || !filter_var($input['original_url'], FILTER_VALIDATE_URL)) {
            $this->sendJSON(['error' => 'URL original inválida o no proporcionada'], 400);
        }

        $originalUrl = $input['original_url'];
        if (strpos($originalUrl, $_SERVER['HTTP_HOST']) !== false) {
            $this->sendJSON(['error' => 'No puedes acortar una URL generada por este dominio'], 400);
        }

        $expiresAt = $input['expires_at'] ?? null; 
        $maxUses = $input['max_uses'] ?? null; 
        $creatorIp = $_SERVER['REMOTE_ADDR'];
        $shortCode = $this->model->createShortUrl($originalUrl, $creatorIp, $expiresAt, $maxUses);
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $shortUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/api/" . $shortCode;

        $this->sendJSON([
            'short_url' => $shortUrl,
            'short_code' => $shortCode,
            'original_url' => $originalUrl
        ], 201);
    }

    public function redirect($shortCode) {
        $urlData = $this->model->getUrlByCode($shortCode);
        if (!$urlData) {
            $this->sendJSON(['error' => 'URL no encontrada'], 404);
        }
        if ($urlData['expires_at'] !== null && strtotime($urlData['expires_at']) < time()) {
            $this->sendJSON(['error' => 'Esta URL ha expirado'], 410); 
        }
        if ($urlData['max_uses'] !== null && $urlData['uses_count'] >= $urlData['max_uses']) {
            $this->sendJSON(['error' => 'Esta URL ha alcanzado su límite de usos permitidos'], 410);
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $this->model->logVisit($urlData['id'], $ip, $userAgent);

        header("Location: " . $urlData['original_url'], true, 302);
        exit;
    }

    public function stats($shortCode) {
        $urlData = $this->model->getUrlByCode($shortCode);
        if (!$urlData) {
            $this->sendJSON(['error' => 'url no encontrada'], 404);
        }

        $stats = $this->model->getStats($urlData['id']);

        $this->sendJSON([
            'short_code' => $shortCode,
            'original_url' => $urlData['original_url'],
            'created_at' => $urlData['created_at'],
            'total_visits' => $urlData['uses_count'],
            'visits_by_day' => $stats['visits_by_day'],
            'last_accesses' => $stats['last_accesses']
        ]);
    }
}
?>