<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Actions\Billing\AssignSubscription;
use App\Actions\Billing\RecordPayment;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The admin billing desk — subscriptions and invoices in one place, for
 * finance/admin. Reads are gated on billing.view (mount); the two
 * mutations (assign a plan, mark an invoice paid) require billing.manage,
 * so a billing.view-only viewer sees the desk read-only. No dedicated
 * Policy: billing spans several models rather than one, so page-level
 * permission checks (the ClinicApplications pattern) are the right fit.
 */
#[Layout('layouts.app', ['title' => 'Billing'])]
class Billing extends Component
{
    use WithPagination;

    /** @var array<int, int> clinic_id => chosen plan_id */
    public array $planFor = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('billing.view'), 403);
    }

    public function assignPlan(int $clinicId, AssignSubscription $assignSubscription): void
    {
        abort_unless(auth()->user()->can('billing.manage'), 403);

        $planId = $this->planFor[$clinicId] ?? null;
        abort_if($planId === null, 422);

        $clinic = Clinic::findOrFail($clinicId);
        $plan = SubscriptionPlan::where('is_active', true)->findOrFail($planId);

        $assignSubscription->handle($clinic, $plan);

        unset($this->planFor[$clinicId]);
    }

    public function markPaid(int $invoiceId, RecordPayment $recordPayment): void
    {
        abort_unless(auth()->user()->can('billing.manage'), 403);

        $invoice = Invoice::findOrFail($invoiceId);
        abort_unless($invoice->status->isPayable(), 403);

        $recordPayment->handle($invoice);
    }

    public function render(): View
    {
        return view('livewire.admin.billing', [
            'plans' => SubscriptionPlan::where('is_active', true)->orderBy('sort')->get(),
            'clinics' => Clinic::query()
                ->with(['activeSubscription.plan'])
                ->orderBy('slug')
                ->get(),
            'invoices' => Invoice::query()
                ->with(['clinic', 'billable'])
                ->latest()
                ->paginate(15),
        ]);
    }
}
