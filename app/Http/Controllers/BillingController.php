<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Plan;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function __construct(private BillingService $service)
    {
    }

    public function index(): Response
    {
        $org = Organization::current();

        return Inertia::render('Billing/Index', [
            'plans'               => Plan::all(),
            'currentSubscription' => $org?->subscription('default'),
            'onTrial'             => $org?->onTrial(),
        ]);
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $org  = Organization::current();
        $plan = Plan::findOrFail($request->plan_id);

        $this->service->subscribe($org, $plan);

        return redirect()->route('billing.index')->with('success', "Subscribed to {$plan->name}.");
    }

    public function cancel(): RedirectResponse
    {
        $org = Organization::current();
        $this->service->cancel($org);

        return redirect()->route('billing.index')->with('success', 'Subscription cancelled.');
    }

    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $event   = $payload['type'] ?? null;

        match ($event) {
            'customer.subscription.updated'  => $this->service->handleSubscriptionUpdated($payload),
            'invoice.payment_failed'         => $this->service->handlePaymentFailed($payload),
            'customer.subscription.deleted'  => $this->service->handleSubscriptionCancelled($payload),
            default                          => null,
        };

        return response()->json(['received' => true]);
    }
}
