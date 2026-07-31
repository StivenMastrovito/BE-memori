<?php

namespace App\Http\Controllers;

use App\Mail\PagePublishedMail;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook: firma non valida.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Firma non valida.'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $payment = Payment::where('provider_payment_id', $session->id)->first();

            if ($payment && $payment->status !== 'succeeded') {
                $payment->update([
                    'status' => 'succeeded',
                    'paid_at' => now(),
                ]);

                $page = $payment->page;
                $page->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'is_published' => true,
                    'published_at' => now(),
                ]);

                try {
                    Mail::to($payment->user->email)->send(new PagePublishedMail($page->fresh()));
                } catch (\Throwable $e) {
                    Log::error('Errore invio email conferma pubblicazione.', [
                        'page_id' => $page->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return response()->json(['received' => true]);
    }
}
