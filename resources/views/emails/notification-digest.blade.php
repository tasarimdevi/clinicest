<x-mail::message>
# Hi {{ $user->name }}, here's what you missed

You have {{ $notifications->count() }} unread {{ $notifications->count() === 1 ? 'notification' : 'notifications' }} on Clinicest:

@foreach ($notifications as $notification)
**{{ $notification->data['title'] ?? '' }}**
{{ $notification->data['body'] ?? '' }}
@if (!$loop->last)

---
@endif
@endforeach

<x-mail::button :url="route('settings.notifications')">
Manage notification settings
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
