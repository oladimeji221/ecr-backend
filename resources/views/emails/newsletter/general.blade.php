<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }} - {{ config('app.name') }}</title>
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
        .newsletter-image {
            width: 100%;
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .newsletter-title {
            font-size: 28px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        .newsletter-content {
            font-size: 16px;
            color: #555555;
            line-height: 1.7;
            margin-bottom: 25px;
        }
        .newsletter-content p {
            margin-bottom: 15px;
        }
        .newsletter-content h1,
        .newsletter-content h2,
        .newsletter-content h3 {
            color: #1a1a1a;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        .newsletter-content a {
            color: #1a1a1a;
            text-decoration: underline;
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
            .newsletter-title {
                font-size: 24px;
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
                We're excited to share some updates with you!
            </p>

            <!-- Newsletter Image -->
            @if($image)
                <img src="{{ $image }}" alt="{{ $subject }}" class="newsletter-image">
            @endif

            <!-- Newsletter Title -->
            <h1 class="newsletter-title">{{ $subject }}</h1>

            <!-- Newsletter Content -->
            <div class="newsletter-content">
                {!! $content !!}
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>You are receiving this email because you subscribed to our newsletter.</p>
            <p><a href="#" class="unsubscribe-link">Unsubscribe</a></p>
        </div>
    </div>
</body>
</html>

