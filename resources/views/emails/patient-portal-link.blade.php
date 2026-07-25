<x-mail::message>
# Track your request, {{ $lead->full_name }}

Thanks for reaching out to Clinicest. Use the link below to check your request status, review offers from matched clinics, manage appointments, and message your clinic directly — no account or password needed.

<x-mail::button :url="$portalUrl">
Open my portal
</x-mail::button>

This link is personal to you — please don't share it. It stays valid for 60 days.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
