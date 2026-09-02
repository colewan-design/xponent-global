<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Queued for the same reason as NewContactEnquiryMail: an applicant submitting
 * a CV should not wait on the mail host. Requires xponent-global-queue.service.
 *
 * `SerializesModels` stores the application by id and re-resolves it when the
 * job runs, restoring the `jobOpening` relation loaded below — so the subject
 * line still has a title to read by the time the worker picks this up.
 */
class NewJobApplicationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public JobApplication $application)
    {
        $this->application->loadMissing('jobOpening');
    }

    public function build(): self
    {
        return $this
            ->subject("New application: {$this->application->jobOpening->title}")
            ->view('emails.new-job-application');
    }
}
