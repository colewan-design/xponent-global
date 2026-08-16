<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NewContactEnquiryMail;
use App\Models\ContactEnquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactEnquiryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enquiry_type' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string'],
            // Honeypot: real users never fill this hidden field in.
            'website' => ['prohibited'],
        ]);

        $enquiry = ContactEnquiry::create($data);

        Mail::to(config('mail.admin_address'))->send(new NewContactEnquiryMail($enquiry));

        return response()->json(['message' => 'Enquiry submitted.', 'id' => $enquiry->id], 201);
    }
}
