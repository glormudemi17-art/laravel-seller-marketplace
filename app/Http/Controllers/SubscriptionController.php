<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function getPlans()
    {
        $plans = [
            [
                'id' => 1,
                'name' => 'Starter',
                'monthly_price' => 29.99,
                'quarterly_price' => 79.99,
                'yearly_price' => 299.99,
                'max_products' => 50,
                'features' => ['50 Products', 'Email & WhatsApp Notifications', 'Basic Analytics'],
            ],
            [
                'id' => 2,
                'name' => 'Professional',
                'monthly_price' => 79.99,
                'quarterly_price' => 199.99,
                'yearly_price' => 799.99,
                'max_products' => 300,
                'features' => ['300 Products', 'Email & WhatsApp Notifications', 'Advanced Analytics', 'Priority Support'],
            ],
            [
                'id' => 3,
                'name' => 'Enterprise',
                'monthly_price' => 199.99,
                'quarterly_price' => 499.99,
                'yearly_price' => 1999.99,
                'max_products' => 1000,
                'features' => ['Unlimited Products', 'All Channels', 'Advanced Analytics', '24/7 Support', 'Custom Integration'],
            ],
        ];

        return response()->json($plans);
    }

    public function subscribe(Request $request)
    {
        $seller = Auth::user()->seller;

        $validated = $request->validate([
            'plan_name' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:monthly,quarterly,yearly',
            'payment_reference' => 'required|string',
        ]);

        // Update seller subscription
        $expiresAt = match($validated['period']) {
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'yearly' => now()->addYear(),
        };

        $seller->update([
            'subscription_fee' => $validated['amount'],
            'subscription_status' => 'active',
            'subscription_expires_at' => $expiresAt,
            'max_products' => $this->getMaxProductsByPlan($validated['plan_name']),
        ]);

        // Create subscription record
        $subscription = Subscription::create([
            'seller_id' => $seller->id,
            'plan_name' => $validated['plan_name'],
            'amount' => $validated['amount'],
            'period' => $validated['period'],
            'started_at' => now(),
            'expires_at' => $expiresAt,
            'status' => 'active',
            'payment_reference' => $validated['payment_reference'],
        ]);

        return response()->json([
            'message' => 'Subscription activated successfully',
            'subscription' => $subscription,
            'seller' => $seller,
        ], 201);
    }

    public function getActiveSubscription()
    {
        $seller = Auth::user()->seller;
        $subscription = $seller->subscriptions()
            ->where('status', 'active')
            ->latest()
            ->first();

        return response()->json($subscription ?? ['message' => 'No active subscription']);
    }

    public function renew(Request $request)
    {
        $seller = Auth::user()->seller;
        $currentSubscription = $seller->subscriptions()
            ->where('status', 'active')
            ->latest()
            ->firstOrFail();

        $validated = $request->validate([
            'payment_reference' => 'required|string',
        ]);

        $expiresAt = match($currentSubscription->period) {
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'yearly' => now()->addYear(),
        };

        $newSubscription = Subscription::create([
            'seller_id' => $seller->id,
            'plan_name' => $currentSubscription->plan_name,
            'amount' => $currentSubscription->amount,
            'period' => $currentSubscription->period,
            'started_at' => now(),
            'expires_at' => $expiresAt,
            'renewed_at' => now(),
            'status' => 'active',
            'payment_reference' => $validated['payment_reference'],
        ]);

        $seller->update([
            'subscription_expires_at' => $expiresAt,
        ]);

        return response()->json([
            'message' => 'Subscription renewed successfully',
            'subscription' => $newSubscription,
        ], 201);
    }

    public function cancel()
    {
        $seller = Auth::user()->seller;

        $seller->update([
            'subscription_status' => 'cancelled',
        ]);

        $seller->subscriptions()
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Subscription cancelled']);
    }

    private function getMaxProductsByPlan(string $planName): int
    {
        return match($planName) {
            'Starter' => 50,
            'Professional' => 300,
            'Enterprise' => 1000,
            default => 50,
        };
    }
}
