<?php
//v2 se supone que ya para v6 y sin USAR NAMESPACE
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
}