<x-mail::message>
# Message from {{ $clinic->getTranslation('name', 'en') }}

{{ $message->body }}

If you have questions, you can reply to this email or contact us via WhatsApp — a member of our team will follow up with you.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
