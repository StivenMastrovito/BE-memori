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
            ->whereHas('featureAddon', fn ($q) => $q->where('key', 'qr_code'))
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

    public function attachments(): array
    {
        if (! $this->hasQrCode) {
            return [];
        }

        $qrCode = new QrCode($this->pageUrl);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return [
            Attachment::fromData(fn () => $result->getString(), 'qr-code.png')
                ->withMime('image/png'),
        ];
    }
}