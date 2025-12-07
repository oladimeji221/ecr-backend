Subject: {{ $subject }}

Hello {{ $recipientName ?? 'Subscriber' }},

{!! strip_tags($content) !!}

@if ($image)
Image: {{ $image }}
@endif

You are receiving this email because you subscribed to our newsletter.
Unsubscribe: {{-- Add unsubscribe link here in the future --}}

Regards,
{{ config('app.name') }}
