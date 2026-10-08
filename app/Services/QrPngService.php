<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

class QrPngService
{
    protected const SCALE = 12;   // pixels per QR module
    protected const QUIET = 4;    // quiet-zone modules around the QR
    protected const LABEL_H = 70; // space under the QR for the code text

    /**
     * URL encoded in the QR for a tag code, e.g. https://admin.famoryapp.com/tag-view/BT70897422
     * Uses the same base URL (APP_URL) as the order and Famory tag QR codes.
     */
    public static function tagUrl(string $code): string
    {
        return rtrim(config('app.url'), '/') . '/tag-view/' . $code;
    }

    /**
     * Render the tag-view URL for $code as a QR code PNG with $label printed underneath.
     * The label is the public reference number, so the tag code itself is never printed.
     * Drawn with GD directly (Imagick is not required).
     */
    public function make(string $code, string $label): string
    {
        $matrix = Encoder::encode(self::tagUrl($code), ErrorCorrectionLevel::M(), 'UTF-8')->getMatrix();
        $modules = $matrix->getWidth();

        $qrSize = ($modules + 2 * self::QUIET) * self::SCALE;
        $img = imagecreatetruecolor($qrSize, $qrSize + self::LABEL_H);

        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $white);

        for ($y = 0; $y < $modules; $y++) {
            for ($x = 0; $x < $modules; $x++) {
                if ($matrix->get($x, $y) === 1) {
                    $px = ($x + self::QUIET) * self::SCALE;
                    $py = ($y + self::QUIET) * self::SCALE;
                    imagefilledrectangle($img, $px, $py, $px + self::SCALE - 1, $py + self::SCALE - 1, $black);
                }
            }
        }

        $this->drawLabel($img, $label, $qrSize, $black);

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return $png;
    }

    protected function drawLabel($img, string $text, int $width, int $color): void
    {
        $font = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        $top = $width; // label area starts right under the QR

        if (is_file($font) && function_exists('imagettftext')) {
            $size = 22;
            $box = imagettfbbox($size, 0, $font, $text);
            $textW = abs($box[2] - $box[0]);
            $x = (int) (($width - $textW) / 2);
            imagettftext($img, $size, 0, $x, $top + 40, $color, $font, $text);
            return;
        }

        // Fallback to GD's built-in font
        $fontId = 5;
        $textW = imagefontwidth($fontId) * strlen($text);
        imagestring($img, $fontId, (int) (($width - $textW) / 2), $top + 20, $text, $color);
    }
}
