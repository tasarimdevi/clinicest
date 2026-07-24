<?php

declare(strict_types=1);

namespace App\Livewire\Patient;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Minimal patient landing page. Full patient portal (offers, messages,
 * appointments, reviews) is Phase 3 — see docs/10-roadmap.md and
 * docs/09-crm-admin-architecture.md §2.
 */
#[Layout('layouts.app', ['title' => 'My Account'])]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.patient.dashboard', [
            'leads' => auth()->user()->leads()->latest()->get(),
        ]);
    }
}
