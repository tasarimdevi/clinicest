<x-mail::message>
# New contact form message

**From:** {{ $senderName }} ({{ $senderEmail }})

{{ $body }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
