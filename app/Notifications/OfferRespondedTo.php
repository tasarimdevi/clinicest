<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OfferRespondedTo extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Offer $offer) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Offer '.$this->offer->status->value,
            'body' => "{$this->offer->lead->full_name} {$this->offer->status->value} the offer \"{$this->offer->title}\".",
            'url' => route('clinic.leads', $this->offer->clinic),
        ];
    }
}
