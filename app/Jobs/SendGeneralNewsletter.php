<?php

namespace App\Jobs;

use App\Mail\GeneralNewsletterMail;
use App\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendGeneralNewsletter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $subject;
    public $content;
    public $image;

    /**
     * Create a new job instance.
     */
    public function __construct(string $subject, string $content, ?string $image = null)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->image = $image;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get all newsletter subscribers
        $subscribers = NewsletterSubscription::all();

        // Send email to each subscriber
        foreach ($subscribers as $subscriber) {
            $recipientName = trim($subscriber->firstname . ' ' . $subscriber->lastname);
            Mail::mailer('noreply')->to($subscriber->email)->send(
                new GeneralNewsletterMail(
                    $this->subject,
                    $this->content,
                    $this->image,
                    $subscriber->email,
                    $recipientName
                )
            );
        }
    }
}
