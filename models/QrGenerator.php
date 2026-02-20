<?php
//v2 se supone que ya para v6 y sin USAR NAMESPACE que sdestroza todooo jajaj
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel; 

class QrGenerator {
    public function generateBasic(string $content, int $size = 300, string $errorCorrectionLevel = 'M') {
        
        $level = match (strtoupper($errorCorrectionLevel)) {
            'L' => ErrorCorrectionLevel::Low,
            'Q' => ErrorCorrectionLevel::Quartile,
            'H' => ErrorCorrectionLevel::High,
            default => ErrorCorrectionLevel::Medium,
        };

        $qrCode = new QrCode(
            data: $content,
            size: $size,
            margin: 10,
            errorCorrectionLevel: $level
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return $result->getString();
    }

    public function buildWifiString(string $ssid, string $password = '', string $type = 'WPA'): string {
        $type = strtoupper($type);
        if ($type === 'NOPASS' || empty($password)) {
            return "WIFI:T:nopass;S:{$ssid};;";
        }
        return "WIFI:T:{$type};S:{$ssid};P:{$password};;";
    }

    public function buildGeoString(float $lat, float $lng): string {
        return "geo:{$lat},{$lng}";
    }

    public function buildEmailString(string $email, string $subject = '', string $body = ''): string {
        return "MATMSG:TO:{$email};SUB:{$subject};BODY:{$body};;";
    }
}