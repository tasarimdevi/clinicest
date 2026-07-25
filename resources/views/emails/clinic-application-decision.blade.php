<x-mail::message>
@if ($clinic->application_status === 'approved')
# You're approved, {{ $clinic->getTranslation('name', 'en') }}!

Your clinic has passed our verification review and is now live on Clinicest.

<x-mail::button :url="route('clinic.dashboard', $clinic)">
Go to your dashboard
</x-mail::button>
@else
# An update on your application

Thanks for applying to list {{ $clinic->getTranslation('name', 'en') }} on Clinicest. After review, we're not able to approve this application right now.

@if ($clinic->rejection_reason)
**Reason:** {{ $clinic->rejection_reason }}
@endif

If you believe this was a mistake or would like to reapply with more information, reply to this email.
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
