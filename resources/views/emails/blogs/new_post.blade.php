<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Blog Post - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background-color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        .content {
            padding: 30px 20px;
        }
        .blog-image {
            width: 100%;
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .blog-title {
            font-size: 28px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        .blog-excerpt {
            font-size: 16px;
            color: #555555;
            line-height: 1.7;
            margin-bottom: 25px;
        }
        .read-more-btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #1a1a1a;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            margin-bottom: 40px;
        }
        .related-blogs {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e0e0e0;
        }
        .related-title {
            font-size: 22px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 25px;
        }
        .related-blog-item {
            margin-bottom: 25px;
            display: block;
            text-decoration: none;
            color: inherit;
        }
        .related-blog-item:last-child {
            margin-bottom: 0;
        }
        .related-blog-content {
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }
        .related-blog-image {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            flex-shrink: 0;
        }
        .related-blog-text {
            flex: 1;
        }
        .related-blog-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 5px;
            line-height: 1.4;
        }
        .footer {
            background-color: #f8f8f8;
            padding: 25px 20px;
            text-align: center;
            font-size: 14px;
            color: #666666;
            border-top: 1px solid #e0e0e0;
        }
        .footer p {
            margin-bottom: 10px;
        }
        .unsubscribe-link {
            color: #666666;
            text-decoration: underline;
        }
        @media only screen and (max-width: 600px) {
            .blog-title {
                font-size: 24px;
            }
            .related-blog-content {
                flex-direction: column;
            }
            .related-blog-image {
                width: 100%;
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header with Logo -->
        <div class="header">
            <img src="{{ url('/images/logo.png') }}" alt="{{ config('app.name') }} Logo" class="logo">
        </div>

        <!-- Main Content -->
        <div class="content">
            @if($recipientName)
                <p style="margin-bottom: 20px; font-size: 16px; color: #555555;">Hello {{ $recipientName }},</p>
            @endif

            <p style="margin-bottom: 25px; font-size: 16px; color: #555555;">
                We're excited to share a new {{ $blog->category ? $blog->category->name : 'blog' }} post with you!
            </p>

            <!-- Blog Image -->
            @if($blog->image)
                <img src="{{ url('storage/' . $blog->image) }}" alt="{{ $blog->title }}" class="blog-image">
            @endif

            <!-- Blog Title -->
            <h1 class="blog-title">{{ $blog->title }}</h1>

            <!-- Blog Excerpt -->
            <div class="blog-excerpt">
                {!! Str::limit(strip_tags($blog->content), 250) !!}
            </div>

            <!-- Read More Button -->
            <a href="{{ url('/insights/' . $blog->slug) }}" class="read-more-btn">Read Full Article</a>

            <!-- Related Blogs Section -->
            @if($relatedBlogs && $relatedBlogs->count() > 0)
                <div class="related-blogs">
                    <h2 class="related-title">You might also like:</h2>
                    @foreach($relatedBlogs as $relatedBlog)
                        <a href="{{ url('/insights/' . $relatedBlog->slug) }}" class="related-blog-item">
                            <div class="related-blog-content">
                                @if($relatedBlog->image)
                                    <img src="{{ url('storage/' . $relatedBlog->image) }}" alt="{{ $relatedBlog->title }}" class="related-blog-image">
                                @else
                                    <div class="related-blog-image" style="background-color: #e0e0e0;"></div>
                                @endif
                                <div class="related-blog-text">
                                    <h3 class="related-blog-title">{{ $relatedBlog->title }}</h3>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>You are receiving this email because you subscribed to {{ $blog->category ? $blog->category->name : 'our' }} newsletter.</p>
            <p><a href="#" class="unsubscribe-link">Unsubscribe</a></p>
        </div>
    </div>
</body>
</html>
