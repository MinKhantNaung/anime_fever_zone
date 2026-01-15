<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;

class SubscriberController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($token, $email)
    {
        $subscriber = Subscriber::where('token', $token)
            ->where('email', $email)
            ->first();

        if ($subscriber && $subscriber->status === 'Pending') {
            $subscriber->token = '';
            $subscriber->status = 'Active';
            $subscriber->update();
        }

        // Return generic message regardless of outcome
        return view('subscription_info', [
            'info' => 'If this email was subscribed, it has been verified.',
        ]);
    }
}
