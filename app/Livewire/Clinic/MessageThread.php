<?php

declare(strict_types=1);

namespace App\Livewire\Clinic;

use App\Actions\Messages\SendLeadMessage;
use App\Models\Clinic;
use App\Models\Lead;
use App\Models\Message;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/09-crm-admin-architecture.md §3 "Patient messages". Same
 * accepted-assignment precondition as the offer builder / appointment
 * scheduler. "Send a reply" is the primary action (always channel=web,
 * really emails the lead); "Log a message" records a conversation that
 * happened elsewhere (phone/WhatsApp/an email reply) so it isn't lost.
 */
#[Layout('layouts.app', ['title' => 'Messages'])]
class MessageThread extends Component
{
    public Clinic $clinic;

    public Lead $lead;

    public string $reply_body = '';

    public bool $logging = false;

    public string $log_channel = 'whatsapp';

    public string $log_direction = 'inbound';

    public string $log_body = '';

    public function mount(Clinic $clinic, Lead $lead): void
    {
        $this->authorize('viewAny', Message::class);

        abort_unless(
            $clinic->leadAssignments()->where('lead_id', $lead->id)->where('status', 'accepted')->exists(),
            403,
            'This lead has not been accepted by your clinic yet.'
        );

        $this->clinic = $clinic;
        $this->lead = $lead;
    }

    public function sendReply(SendLeadMessage $sendLeadMessage): void
    {
        $this->authorize('create', Message::class);

        $validated = $this->validate([
            'reply_body' => ['required', 'string', 'max:5000'],
        ]);

        $sendLeadMessage->handle($this->lead, $this->clinic, [
            'direction' => 'outbound',
            'channel' => 'web',
            'body' => $validated['reply_body'],
        ], auth()->user());

        $this->reset('reply_body');
    }

    public function logMessage(SendLeadMessage $sendLeadMessage): void
    {
        $this->authorize('create', Message::class);

        $validated = $this->validate([
            'log_channel' => ['required', Rule::in(['email', 'whatsapp', 'call'])],
            'log_direction' => ['required', Rule::in(['inbound', 'outbound'])],
            'log_body' => ['required', 'string', 'max:5000'],
        ]);

        $sendLeadMessage->handle($this->lead, $this->clinic, [
            'direction' => $validated['log_direction'],
            'channel' => $validated['log_channel'],
            'body' => $validated['log_body'],
        ], auth()->user());

        $this->reset('log_body');
        $this->logging = false;
    }

    public function render(): View
    {
        return view('livewire.clinic.message-thread', [
            'messages' => Message::where('lead_id', $this->lead->id)
                ->where('clinic_id', $this->clinic->id)
                ->with('sender')
                ->oldest('created_at')
                ->get(),
        ]);
    }
}
