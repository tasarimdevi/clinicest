<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\NotificationDigestMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * docs/09-crm-admin-architecture.md §5's "digest emails" — everything a
 * user hasn't yet read in their notification bell, batched into one daily
 * email instead of one email per event (see the Notification classes'
 * via() methods: most only ever go to the 'database' channel directly,
 * relying entirely on this command for email delivery). Idempotent by
 * design: it digests whatever is still unread, so re-running it (or a
 * user not opening the bell for a week) just repeats the same items
 * rather than losing or duplicating anything.
 */
class SendNotificationDigest extends Command
{
    protected $signature = 'notifications:digest';

    protected $description = 'Email each user a summary of their unread in-app notifications';

    public function handle(): int
    {
        $sent = 0;

        User::query()
            ->whereHas('unreadNotifications')
            ->with('unreadNotifications')
            ->each(function (User $user) use (&$sent) {
                if (! $user->wantsDigest()) {
                    return;
                }

                $unread = $user->unreadNotifications;

                if ($unread->isEmpty()) {
                    return;
                }

                Mail::to($user->email)->send(new NotificationDigestMail($user, $unread));
                $sent++;
            });

        $this->info("Sent {$sent} digest email(s).");

        return self::SUCCESS;
    }
}
