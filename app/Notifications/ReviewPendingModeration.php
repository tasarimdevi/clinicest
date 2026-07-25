<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReviewPendingModeration extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Review $review) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Review awaiting moderation',
            'body' => "{$this->review->reviewer_name} left a {$this->review->rating}-star review.",
            'url' => route('admin.reviews.index'),
        ];
    }
}
