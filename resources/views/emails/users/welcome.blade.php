<x-mail::message>
# Welcome to {{ config('app.name') }}

Hello **{{ $user->name }}**,

Thank you for creating an account. We are excited to have you on board!

Our platform offers a variety of features to help you manage your content and engage with our community. We encourage you to explore your new dashboard and start creating.

<x-mail::button :url="config('app.url')">
Go to Your Dashboard
</x-mail::button>

If you have any questions or need assistance, please do not hesitate to contact our support team.

Thanks,<br>
The {{ config('app.name') }} Team

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.

**Contact Us:**
{{ config('mail.from.address') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::message>