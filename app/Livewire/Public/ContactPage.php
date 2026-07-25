<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Mail\ContactMessageMail;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §13. No ContactMessage model/admin inbox yet
 * (not scoped in docs/10-roadmap.md) — the outbound mail (MAIL_MAILER=log
 * locally) is the record for now, same reduced scope as the rest of the
 * public site's Phase 1/2 pages.
 */
#[Layout('layouts.public')]
class ContactPage extends Component
{
    public string $full_name = '';

    public string $email = '';

    public string $message = '';

    public bool $sent = false;

    protected function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    public function submit(): void
    {
        $validated = $this->validate();

        Mail::to(config('clinicest.contact_email'))->send(new ContactMessageMail(
            senderName: $validated['full_name'],
            senderEmail: $validated['email'],
            body: $validated['message'],
        ));

        $this->sent = true;
        $this->reset(['full_name', 'email', 'message']);
    }

    public function render(): View
    {
        return view('livewire.public.contact-page');
    }
}
