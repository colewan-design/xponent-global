<?php

namespace App\Mail;

use App\Models\ContactEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Queued deliberately. Sending inline made the visitor wait for the round trip
 * to the mail host — measured at ~3.4s of a 3.8s response — and coupled the
 * contact form's success to the mail provider being up.
 *
 * This requires xponent-global-queue.service to be running. Without a worker
 * the notification is not lost (it sits in the `jobs` table) but it is never
 * delivered either, so the two must always be deployed together.
 */
class NewContactEnquiryMail extends Mailable implements ShouldQueue
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
