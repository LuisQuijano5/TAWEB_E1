<?php
require_once __DIR__ . '/../config/db.php';

class UrlShortener {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    private function generateCode($length = 6): string {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    public function createShortUrl($originalUrl, $creatorIp, $expiresAt = null, $maxUses = null, $length = 6) {
        $isUnique = false;
        $shortCode = '';

        while (!$isUnique) {
            $shortCode = $this->generateCode($length);
            $stmt = $this->conn->prepare("SELECT id FROM urls WHERE short_code = ?");
            $stmt->execute([$shortCode]);
            if ($stmt->rowCount() === 0) {
                $isUnique = true;
            }
        }

        $sql = "INSERT INTO urls (original_url, short_code, creator_ip, expires_at, max_uses) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$originalUrl, $shortCode, $creatorIp, $expiresAt, $maxUses]);

        return $shortCode;
    }

    public function getUrlByCode($shortCode) {
        $stmt = $this->conn->prepare("SELECT * FROM urls WHERE short_code = ?");
        $stmt->execute([$shortCode]);
        return $stmt->fetch();
    }

    public function logVisit($urlId, $ipAddress, $userAgent) {
        $stmt = $this->conn->prepare("UPDATE urls SET uses_count = uses_count + 1 WHERE id = ?");
        $stmt->execute([$urlId]);
        $sql = "INSERT INTO url_visits (url_id, ip_address, user_agent) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$urlId, $ipAddress, $userAgent]);
    }

    public function getStats($urlId) {
        $stmt = $this->conn->prepare("SELECT DATE(visit_date) as date, COUNT(*) as count FROM url_visits WHERE url_id = ? GROUP BY DATE(visit_date) ORDER BY date DESC");
        $stmt->execute([$urlId]);
        $visitas = $stmt->fetchAll();

        $stmt2 = $this->conn->prepare("SELECT visit_date, ip_address, user_agent FROM url_visits WHERE url_id = ? ORDER BY visit_date DESC LIMIT 5");
        $stmt2->execute([$urlId]);
        $ultimo = $stmt2->fetchAll();

        return [
            'visitas_dia' => $visitas,
            'ultimo_acceso' => $ultimo
        ];
    }
}