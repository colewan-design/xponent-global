<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #1d1d1d;">
    <h2>New contact enquiry</h2>
    <p><strong>Enquiring about:</strong> {{ $enquiry->enquiry_type }}</p>
    <p><strong>Name:</strong> {{ $enquiry->name }}</p>
    <p><strong>Email:</strong> {{ $enquiry->email }}</p>
    @if ($enquiry->company)
        <p><strong>Company:</strong> {{ $enquiry->company }}</p>
    @endif
    @if ($enquiry->phone)
        <p><strong>Phone:</strong> {{ $enquiry->phone }}</p>
    @endif
    <p><strong>Region / Country:</strong> {{ $enquiry->region }} / {{ $enquiry->country }}</p>
    <p><strong>Message:</strong></p>
    <p style="white-space: pre-wrap; background: #f5f5f5; padding: 12px; border-radius: 6px;">{{ $enquiry->message }}</p>
    <p style="color: #6b6b6b; font-size: 13px;">View and manage this enquiry in the admin console.</p>
</body>
</html>
