<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\Resource as ResourceModel;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'new_enquiries' => ContactEnquiry::where('status', 'new')->count(),
            'total_enquiries' => ContactEnquiry::count(),
            'subscribers' => NewsletterSubscriber::where('status', 'subscribed')->count(),
            'open_jobs' => JobOpening::where('status', 'open')->count(),
            'new_applications' => JobApplication::where('status', 'new')->count(),
            'published_posts' => Post::where('published', true)->count(),
            'resources' => ResourceModel::count(),
        ]);
    }
}
