<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\BeforeAfterCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BeforeAfterPendingModeration extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BeforeAfterCase $case) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Before/after case awaiting moderation',
            'body' => "{$this->case->clinic->getTranslation('name', 'en')} submitted a before/after case.",
            'url' => route('admin.before-after.index'),
        ];
    }
}
