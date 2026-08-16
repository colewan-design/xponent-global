<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;

class NewsletterSubscriberController extends Controller
{
    public function index()
    {
        $subscribers = NewsletterSubscriber::orderByDesc('created_at')->paginate(30);

        return NewsletterSubscriberResource::collection($subscribers);
    }

    public function destroy(NewsletterSubscriber $newsletterSubscriber): JsonResponse
    {
        $newsletterSubscriber->delete();

        return response()->json(['message' => 'Subscriber deleted.']);
    }
}
