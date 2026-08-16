<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewJobApplicationMail extends Mailable
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
