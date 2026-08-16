<?php

namespace App\Mail;

use App\Models\ContactEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewContactEnquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactEnquiry $enquiry)
    {
    }

    public function build(): self
    {
        return $this
            ->subject("New enquiry: {$this->enquiry->enquiry_type}")
            ->view('emails.new-contact-enquiry');
    }
}
