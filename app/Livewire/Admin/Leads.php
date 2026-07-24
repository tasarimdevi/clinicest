<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * CRM lead inbox — see docs/09-crm-admin-architecture.md §2.
 */
#[Layout('layouts.app', ['title' => 'Leads'])]
class Leads extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Lead::class);
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $leads = Lead::query()
            ->with(['primaryTreatment', 'country', 'assignedAgent'])
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('full_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(20);

        return view('livewire.admin.leads', [
            'leads' => $leads,
            'statuses' => LeadStatus::cases(),
        ]);
    }
}
