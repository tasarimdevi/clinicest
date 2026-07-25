<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * The in-app half of docs/09-crm-admin-architecture.md §5's "unified
 * notifications" — database-backed only (no websocket/broadcast channel
 * wired), so "real-time" here means "current as of the last page load /
 * poll", not a live push. See User::notifications() (Notifiable trait,
 * previously unused) and the notifications migration.
 */
class NotificationBell extends Component
{
    public function markAsRead(string $id): void
    {
        auth()->user()->notifications()->whereKey($id)->first()?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.notification-bell', [
            'notifications' => $user->notifications()->limit(10)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
