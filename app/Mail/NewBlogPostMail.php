<?php

namespace App\Mail;

use App\Models\Blog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewBlogPostMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The blog instance.
     *
     * @var \App\Models\Blog
     */
    public $blog;

    /**
     * The recipient's email address.
     *
     * @var string
     */
    public $recipientEmail;

    /**
     * The recipient's name (optional).
     *
     * @var string|null
     */
    public $recipientName;

    /**
     * Related blogs from the same category.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    public $relatedBlogs;

    /**
     * Create a new message instance.
     */
    public function __construct(Blog $blog, string $recipientEmail, ?string $recipientName = null, $relatedBlogs = null)
    {
        $this->blog = $blog->load('category', 'user');
        $this->recipientEmail = $recipientEmail;
        $this->recipientName = $recipientName;
        $this->relatedBlogs = $relatedBlogs ?? collect();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Get the blog author's name, or fallback to app name
        $senderName = $this->blog->user ? $this->blog->user->name : config('app.name');
        $senderEmail = config('mail.noreply_from.address', 'no_reply@ecr-ts.com');
        
        return new Envelope(
            from: new Address($senderEmail, $senderName),
            subject: 'New ' . ($this->blog->category ? $this->blog->category->name : 'Blog') . ' Post: ' . $this->blog->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.blogs.new_post',
            with: [
                'blog' => $this->blog,
                'recipientEmail' => $this->recipientEmail,
                'recipientName' => $this->recipientName,
                'relatedBlogs' => $this->relatedBlogs,
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