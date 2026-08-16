<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterSubscriberController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $subscriber = NewsletterSubscriber::updateOrCreate(
            ['email' => $data['email']],
            ['status' => 'subscribed'],
        );

        return response()->json(['message' => 'Subscribed.', 'id' => $subscriber->id], 201);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        NewsletterSubscriber::where('email', $data['email'])->update(['status' => 'unsubscribed']);

        // Always return success, whether or not the email was subscribed —
        // otherwise this endpoint could be used to check which emails exist.
        return response()->json(['message' => 'You have been unsubscribed.']);
    }
}
