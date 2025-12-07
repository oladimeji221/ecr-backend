<?php

namespace App\Jobs;

use App\Mail\NewBlogPostMail;
use App\Models\Blog;
use App\Models\CategorySubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NotifyUsersOfNewPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The blog instance.
     *
     * @var \App\Models\Blog
     */
    protected $blog;

    /**
     * Create a new job instance.
     */
    public function __construct(Blog $blog)
    {
        $this->blog = $blog;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Only send to category-specific subscribers for this blog's category
        if (!$this->blog->category_id) {
            return;
        }

        // Get category subscribers with their names
        $categorySubscribers = CategorySubscription::where('category_id', $this->blog->category_id)
                                                    ->get();

        // Get 3 related blogs from the same category (excluding current blog)
        $relatedBlogs = Blog::where('category_id', $this->blog->category_id)
                            ->where('id', '!=', $this->blog->id)
                            ->where('status', 'published')
                            ->with('category')
                            ->latest()
                            ->take(3)
                            ->get();

        // Send email to each category subscriber
        foreach ($categorySubscribers as $subscriber) {
            $recipientName = trim($subscriber->firstname . ' ' . $subscriber->lastname);
            Mail::mailer('noreply')->to($subscriber->email)->send(
                new NewBlogPostMail($this->blog, $subscriber->email, $recipientName, $relatedBlogs)
            );
        }
    }
}