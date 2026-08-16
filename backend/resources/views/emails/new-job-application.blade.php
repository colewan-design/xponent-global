<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1d1d1d;">
    <h2>New job application</h2>
    <p><strong>Position:</strong> {{ $application->jobOpening->title }}</p>
    <p><strong>Applicant:</strong> {{ $application->name }}</p>
    <p><strong>Email:</strong> {{ $application->email }}</p>
    @if ($application->phone)
        <p><strong>Phone:</strong> {{ $application->phone }}</p>
    @endif
    @if ($application->cover_letter)
        <p><strong>Cover letter:</strong></p>
        <p style="white-space: pre-wrap; background: #f5f5f5; padding: 12px; border-radius: 6px;">{{ $application->cover_letter }}</p>
    @endif
    <p style="color: #6b6b6b; font-size: 13px;">Download the resume and manage this application in the admin console.</p>
</body>
</html>
