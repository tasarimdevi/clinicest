<?php

declare(strict_types=1);

namespace App\Actions\Messages;

use App\Mail\LeadMessageMail;
use App\Models\Clinic;
use App\Models\Lead;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Mirrors CreateOffer/RequestAppointment's shape. `channel=web` +
 * `direction=outbound` is the one case that actually sends something (a
 * real email to the lead — there's no patient portal to deliver an
 * in-app message to); every other channel/direction combination is a
 * staff member logging a conversation that happened elsewhere (a phone
 * call, a WhatsApp exchange, or an email reply received outside this
 * system), so nothing about the conversation gets lost even without a
 * real WhatsApp/email bridge. See the messages migration docblock.
 */
class SendLeadMessage
{
    /**
     * @param  array{direction: string, channel: string, body: string}  $data
     */
    public function handle(Lead $lead, Clinic $clinic, array $data, User $sender): Message
    {
        return DB::transaction(function () use ($lead, $clinic, $data, $sender) {
            $message = Message::create([
                'lead_id' => $lead->id,
                'clinic_id' => $clinic->id,
                'sender_type' => $sender::class,
                'sender_id' => $sender->id,
                'direction' => $data['direction'],
                'channel' => $data['channel'],
                'body' => $data['body'],
                'created_at' => now(),
            ]);

            if ($data['channel'] === 'web' && $data['direction'] === 'outbound') {
                Mail::to($lead->email)->send(new LeadMessageMail($clinic, $message));
            }

            $lead->activities()->create([
                'actor_type' => $sender::class,
                'actor_id' => $sender->id,
                // LeadActivity's type enum has no 'web' value — a web
                // message is delivered as email, so it's logged as one.
                'type' => $data['channel'] === 'web' ? 'email' : $data['channel'],
                'payload_json' => ['event' => 'message', 'direction' => $data['direction'], 'clinic_id' => $clinic->id, 'message_id' => $message->id],
                'created_at' => now(),
            ]);

            return $message;
        });
    }
}
