<?php

namespace App\Mail;

use App\Models\Page;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
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
            $qrCode = new QrCode($this->pageUrl);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            $framedImage = $this->addFrame($result->getString());

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

    private function addFrame(string $qrPngData): string
    {
        $padding = 60;        // spazio attorno al QR
        $labelHeight = 50;    // spazio per il testo sotto
        $borderRadius = 24;   // angoli arrotondati della cornice

        $qrImage = imagecreatefromstring($qrPngData);
        $qrWidth = imagesx($qrImage);
        $qrHeight = imagesy($qrImage);

        $canvasWidth = $qrWidth + ($padding * 2);
        $canvasHeight = $qrHeight + ($padding * 2) + $labelHeight;

        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        // Sfondo bianco con angoli arrotondati
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $canvasWidth, $canvasHeight, $white);

        // Bordo colorato (personalizzabile con il colore del tema, qui un esempio fisso)
        $borderColor = imagecolorallocate($canvas, 191, 70, 235); // es. --page-primary
        imagesetthickness($canvas, 6);
        imagerectangle($canvas, 3, 3, $canvasWidth - 4, $canvasHeight - 4, $borderColor);

        // Incolla il QR al centro
        imagecopy($canvas, $qrImage, $padding, $padding, 0, 0, $qrWidth, $qrHeight);

        // Testo sotto il QR
        $textColor = imagecolorallocate($canvas, 44, 44, 44);
        $font = 5; // font GD built-in (1-5, nessun file esterno richiesto)
        $text = 'Inquadrami per aprire la pagina';
        $textWidth = imagefontwidth($font) * strlen($text);
        $textX = (int) (($canvasWidth - $textWidth) / 2);
        $textY = $canvasHeight - $labelHeight + 15;
        imagestring($canvas, $font, $textX, $textY, $text, $textColor);

        ob_start();
        imagepng($canvas);
        $output = ob_get_clean();

        imagedestroy($qrImage);
        imagedestroy($canvas);

        return $output;
    }
}
