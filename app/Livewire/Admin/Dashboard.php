<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\LeadStatus;
use App\Models\Clinic;
use App\Models\Lead;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/09-crm-admin-architecture.md §4 — platform KPIs at a glance.
 */
#[Layout('layouts.app', ['title' => 'Dashboard'])]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'openLeads' => Lead::query()->whereNotIn('status', ['won', 'lost', 'invalid'])->count(),
            'newLeads' => Lead::query()->where('status', LeadStatus::New)->count(),
            'wonLeads' => Lead::query()->where('status', LeadStatus::Won)->count(),
            'activeClinics' => Clinic::query()->where('is_active', true)->count(),
        ]);
    }
}
