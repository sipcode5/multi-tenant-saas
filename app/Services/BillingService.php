<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;

class BillingService
{
    public function subscribe(Organization $organization, Plan $plan, ?string $paymentMethodId = null): void
    {
        $subscription = $organization->newSubscription('default', $plan->stripe_price_id);

        if ($paymentMethodId) {
            $subscription->create($paymentMethodId);
        } else {
            // Trial / mock mode — skip Stripe API call, create a test subscription record
            $organization->subscriptions()->create([
                'type'          => 'default',
                'stripe_id'     => 'sub_mock_' . uniqid(),
                'stripe_status' => 'active',
                'stripe_price'  => $plan->stripe_price_id,
                'quantity'      => 1,
                'plan_id'       => $plan->id,
            ]);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($organization)
            ->withProperties(['plan' => $plan->name, 'organization_id' => $organization->id])
            ->log('subscribed to plan');
    }

    public function cancel(Organization $organization): void
    {
        $organization->subscription('default')?->cancel();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($organization)
            ->withProperties(['organization_id' => $organization->id])
            ->log('cancelled subscription');
    }

    public function handleSubscriptionUpdated(array $payload): void
    {
        $stripeId = $payload['data']['object']['id'] ?? null;
        if (!$stripeId) {
            return;
        }
        Log::info('Stripe subscription updated', ['stripe_id' => $stripeId]);
    }

    public function handlePaymentFailed(array $payload): void
    {
        Log::warning('Stripe payment failed', $payload['data']['object'] ?? []);
    }

    public function handleSubscriptionCancelled(array $payload): void
    {
        Log::info('Stripe subscription cancelled', $payload['data']['object'] ?? []);
    }
}
