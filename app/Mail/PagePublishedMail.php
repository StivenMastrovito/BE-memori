<?php

namespace App\Mail;

use App\Models\Page;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PagePublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $pageUrl;
    public bool $hasQrCode;

    public function __construct(public Page $page)
    {
        $this->pageUrl = rtrim(config('services.frontend_url', env('FRONTEND_URL')), '/') . '/p/' . $page->slug;

        $this->hasQrCode = $page->addons()
            ->whereHas('featureAddon', fn($q) => $q->where('key', 'qr_code'))
            ->exists();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'La tua pagina "' . $this->page->title . '" è pronta!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.page-published',
            with: [
                'title' => $this->page->title,
                'pageUrl' => $this->pageUrl,
            ],
        );
    }

    // public function attachments(): array
    // {
    //     if (! $this->hasQrCode) {
    //         return [];
    //     }

    //     $qrCode = new QrCode($this->pageUrl);
    //     $writer = new PngWriter();
    //     $result = $writer->write($qrCode);

    //     return [
    //         Attachment::fromData(fn () => $result->getString(), 'qr-code.png')
    //             ->withMime('image/png'),
    //     ];
    // }
    public function attachments(): array
    {
        if (! $this->hasQrCode) {
            return [];
        }

        try {
            $framedImage = $this->generateBrandedFramedQrCode();

            return [
                Attachment::fromData(fn() => $framedImage, 'qr-code.png')
                    ->withMime('image/png'),
            ];
        } catch (\Throwable $e) {
            Log::error('Errore nella generazione del QR code per l\'email.', [
                'page_id' => $this->page->id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function generateBrandedFramedQrCode(): string
    {
        $innerX = 165;
        $innerY = 195;
        $innerWidth = 740;
        $innerHeight = 640;
        $margin = 60;
        $targetSize = min($innerWidth, $innerHeight) - ($margin * 2);

        $qrCode = new QrCode(
            data: $this->pageUrl,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            size: $targetSize,
            margin: 10,
        );
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $rawQr = imagecreatefromstring($result->getString());
        $qrSize = imagesx($rawQr);

        // 1. Appiattisci su una tela bianca opaca, per eliminare ogni ambiguità di trasparenza
        $qrImage = imagecreatetruecolor($qrSize, $qrSize);
        $white = imagecolorallocate($qrImage, 255, 255, 255);
        imagefill($qrImage, 0, 0, $white);
        imagecopy($qrImage, $rawQr, 0, 0, 0, 0, $qrSize, $qrSize);
        imagedestroy($rawQr);

        // 2. Colora i moduli scuri con il gradiente
        $this->applyGradientToQr($qrImage);

        // 3. Logo al centro con sfondo bianco protettivo
        $this->pasteLogoOnQr($qrImage);

        // 4. Compone dentro la cornice
        $framePath = resource_path('images/qr-frame.png');
        $frame = imagecreatefrompng($framePath);
        imagesavealpha($frame, true);

        $pasteX = $innerX + (int) (($innerWidth - $targetSize) / 2);
        $pasteY = $innerY + (int) (($innerHeight - $targetSize) / 2);
        imagecopy($frame, $qrImage, $pasteX, $pasteY, 0, 0, $targetSize, $targetSize);

        ob_start();
        imagepng($frame);
        $output = ob_get_clean();

        imagedestroy($qrImage);
        imagedestroy($frame);

        return $output;
    }

    private function applyGradientToQr($qrImage): void
    {
        $width = imagesx($qrImage);
        $height = imagesy($qrImage);

        $colorStart = ['r' => 34, 'g' => 211, 'b' => 238];
        $colorEnd   = ['r' => 147, 'g' => 51, 'b' => 234];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($qrImage, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // Ora che l'immagine è appiattita su bianco, basta la luminanza per rilevare i pixel scuri
                $isDark = ($r + $g + $b) < 250;

                if (! $isDark) {
                    continue;
                }

                $t = ($x + $y) / ($width + $height);
                $newR = (int) ($colorStart['r'] + ($colorEnd['r'] - $colorStart['r']) * $t);
                $newG = (int) ($colorStart['g'] + ($colorEnd['g'] - $colorStart['g']) * $t);
                $newB = (int) ($colorStart['b'] + ($colorEnd['b'] - $colorStart['b']) * $t);

                $newColor = imagecolorallocate($qrImage, $newR, $newG, $newB);
                imagesetpixel($qrImage, $x, $y, $newColor);
            }
        }
    }

    private function pasteLogoOnQr($qrImage): void
    {
        $logoPath = resource_path('images/heart-logo.png');

        if (! file_exists($logoPath)) {
            return;
        }

        $qrSize = imagesx($qrImage);
        $logoAreaSize = (int) ($qrSize * 0.14);
        $circlePadding = 8;
        $circleSize = $logoAreaSize + ($circlePadding * 2);

        $centerX = (int) ($qrSize / 2);
        $centerY = (int) ($qrSize / 2);

        $white = imagecolorallocate($qrImage, 255, 255, 255);
        imagefilledellipse($qrImage, $centerX, $centerY, $circleSize, $circleSize, $white);

        $logo = imagecreatefrompng($logoPath);
        imagesavealpha($logo, true);
        imagealphablending($logo, true);

        $resizedLogo = imagecreatetruecolor($logoAreaSize, $logoAreaSize);
        imagesavealpha($resizedLogo, true);
        $transparent = imagecolorallocatealpha($resizedLogo, 255, 255, 255, 127);
        imagefill($resizedLogo, 0, 0, $transparent);
        imagealphablending($resizedLogo, false);

        imagecopyresampled(
            $resizedLogo,
            $logo,
            0,
            0,
            0,
            0,
            $logoAreaSize,
            $logoAreaSize,
            imagesx($logo),
            imagesy($logo)
        );

        imagealphablending($qrImage, true);
        imagecopy(
            $qrImage,
            $resizedLogo,
            $centerX - (int) ($logoAreaSize / 2),
            $centerY - (int) ($logoAreaSize / 2),
            0,
            0,
            $logoAreaSize,
            $logoAreaSize
        );

        imagedestroy($logo);
        imagedestroy($resizedLogo);
    }
}
