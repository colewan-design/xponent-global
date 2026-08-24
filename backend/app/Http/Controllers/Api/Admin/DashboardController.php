<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use App\Models\InventoryItem;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
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

            // Commerce. "Open" is everything still being worked — delivered and
            // cancelled orders are done with and drop out of the count.
            'open_orders' => Order::whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'orders_awaiting_payment' => Order::whereIn('payment_status', ['unpaid', 'partial'])
                ->where('status', '!=', 'cancelled')
                ->count(),
            // Value of what is committed but not yet delivered — the figure
            // that answers "what is in the pipeline".
            'open_order_value' => round((float) Order::whereNotIn('status', ['delivered', 'cancelled'])->sum('total'), 2),
            'active_products' => Product::active()->count(),
            'low_stock_items' => InventoryItem::lowStock()->count(),
        ]);
    }
}
