<?php

declare(strict_types=1);

namespace App\Livewire\Clinic;

use App\Models\Clinic;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The clinic's own billing view — current plan, available plans, and its
 * invoices. Deliberately read-only: with no payment provider wired, a
 * clinic self-selecting a plan would amount to granting itself a paid tier
 * for free, so plan changes go through staff (admin Billing desk,
 * AssignSubscription). Gated on billing.view (clinic_owner holds it) plus
 * the clinic.member route middleware.
 */
#[Layout('layouts.app', ['title' => 'Billing'])]
class ClinicBilling extends Component
{
    public Clinic $clinic;

    public function mount(Clinic $clinic): void
    {
        abort_unless(auth()->user()->can('billing.view'), 403);

        $this->clinic = $clinic;
    }

    public function render(): View
    {
        return view('livewire.clinic.clinic-billing', [
            'subscription' => $this->clinic->activeSubscription()->with('plan')->first(),
            'plans' => SubscriptionPlan::where('is_active', true)->orderBy('sort')->get(),
            'invoices' => $this->clinic->invoices()->with('billable')->latest()->get(),
        ]);
    }
}
