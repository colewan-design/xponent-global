<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NewJobApplicationMail;
use App\Models\JobOpening;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class JobApplicationController extends Controller
{
    public function store(Request $request, JobOpening $jobOpening): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'cover_letter' => ['nullable', 'string'],
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $data['resume'] = $request->file('resume')->store('resumes', 'public');

        $application = $jobOpening->applications()->create($data);

        Mail::to(config('mail.admin_address'))->send(new NewJobApplicationMail($application));

        return response()->json(['message' => 'Application submitted.', 'id' => $application->id], 201);
    }
}
