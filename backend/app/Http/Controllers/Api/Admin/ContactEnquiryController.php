<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactEnquiryResource;
use App\Models\ContactEnquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactEnquiryController extends Controller
{
    public function index(Request $request)
    {
        $enquiries = ContactEnquiry::query()
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20);

        return ContactEnquiryResource::collection($enquiries);
    }

    public function show(ContactEnquiry $contactEnquiry): ContactEnquiryResource
    {
        return new ContactEnquiryResource($contactEnquiry);
    }

    public function update(Request $request, ContactEnquiry $contactEnquiry): ContactEnquiryResource
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,contacted,closed'],
        ]);

        $contactEnquiry->update($data);

        return new ContactEnquiryResource($contactEnquiry);
    }

    public function destroy(ContactEnquiry $contactEnquiry): JsonResponse
    {
        $contactEnquiry->delete();

        return response()->json(['message' => 'Enquiry deleted.']);
    }
}
