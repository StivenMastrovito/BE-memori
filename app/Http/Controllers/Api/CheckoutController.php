<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Payment;
use App\Models\PaymentItem;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function store(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        if ($page->payment_status === 'paid') {
            return response()->json(['message' => 'Questa pagina è già stata pagata.'], 409);
        }

        if ((float) $page->total_amount <= 9) {
            return response()->json(['message' => 'Il totale della pagina non è valido.'], 422);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        // Costruisce i line items dettagliati per la ricevuta Stripe
        $lineItems = [[
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => 'Pubblicazione pagina: ' . $page->title],
                'unit_amount' => (int) round($page->base_price * 100),
            ],
            'quantity' => 1,
        ]];

        foreach ($page->sections()->where('price', '>', 0)->get() as $section) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => 'Sezione: ' . ucfirst($section->type)],
                    'unit_amount' => (int) round($section->price * 100),
                ],
                'quantity' => 1,
            ];
        }

        foreach ($page->addons()->with('featureAddon')->get() as $addon) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => $addon->featureAddon->name],
                    'unit_amount' => (int) round($addon->price * 100),
                ],
                'quantity' => 1,
            ];
        }

        // Crea il record di pagamento in stato pending PRIMA del redirect a Stripe
        $payment = Payment::create([
            'user_id' => $request->user()->id,
            'page_id' => $page->id,
            'amount' => $page->total_amount,
            'currency' => 'EUR',
            'provider' => 'stripe',
            'status' => 'pending',
        ]);

        PaymentItem::create(['payment_id' => $payment->id, 'label' => 'Pubblicazione pagina base', 'price' => $page->base_price]);
        foreach ($page->sections()->where('price', '>', 0)->get() as $section) {
            PaymentItem::create(['payment_id' => $payment->id, 'label' => 'Sezione: ' . ucfirst($section->type), 'price' => $section->price]);
        }
        foreach ($page->addons()->with('featureAddon')->get() as $addon) {
            PaymentItem::create(['payment_id' => $payment->id, 'label' => $addon->featureAddon->name, 'price' => $addon->price]);
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => config('services.frontend_url', env('FRONTEND_URL')) . '/pages/' . $page->id . '/payment-success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('services.frontend_url', env('FRONTEND_URL')) . '/pages/' . $page->id . '/edit',
            'metadata' => [
                'payment_id' => $payment->id,
                'page_id' => $page->id,
            ],
        ]);

        $payment->update(['provider_payment_id' => $session->id]);

        return response()->json(['checkout_url' => $session->url]);
    }
}