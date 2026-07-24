<?php

declare(strict_types=1);

namespace App\Livewire\Clinic;

use App\Models\Clinic;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/09-crm-admin-architecture.md §3 — clinic partner overview KPIs.
 */
#[Layout('layouts.app', ['title' => 'Clinic Dashboard'])]
class Dashboard extends Component
{
    public Clinic $clinic;

    public function mount(Clinic $clinic): void
    {
        $this->clinic = $clinic;
    }

    public function render()
    {
        return view('livewire.clinic.dashboard', [
            'pendingAssignments' => $this->clinic->leadAssignments()->where('status', 'offered')->count(),
            'acceptedAssignments' => $this->clinic->leadAssignments()->where('status', 'accepted')->count(),
            'verificationTier' => $this->clinic->verification_tier,
            'rating' => $this->clinic->rating_avg,
        ]);
    }
}
