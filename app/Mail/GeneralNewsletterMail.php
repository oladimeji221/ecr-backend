<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GeneralNewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $content;
    public $image;
    public $recipientName;
    public $recipientEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $content, ?string $image = null, string $recipientEmail, ?string $recipientName = null)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->image = $image;
        $this->recipientEmail = $recipientEmail;
        $this->recipientName = $recipientName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $senderName = config('app.name');
        $senderEmail = config('mail.noreply_from.address', 'no_reply@ecr-ts.com');

        return new Envelope(
            from: new Address($senderEmail, $senderName),
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter.general',
            text: 'emails.newsletter.general_plain',
            with: [
                'subject' => $this->subject,
                'content' => $this->content,
                'image' => $this->image,
                'recipientName' => $this->recipientName,
                'recipientEmail' => $this->recipientEmail,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}