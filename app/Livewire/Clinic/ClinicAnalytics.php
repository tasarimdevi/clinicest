<?php

declare(strict_types=1);

namespace App\Livewire\Clinic;

use App\Models\Clinic;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Clinic-side performance analytics — the "analytics" a subscription
 * promises (docs/01-product-strategy.md §3). Every figure is scoped to
 * this clinic and to the selected time window. Gated on clinics.manage
 * (the clinic owner), matching how the Profile / Before-After pages are
 * gated — a clinic_manager/staff doesn't see business metrics.
 *
 * Tier-gating (analytics as a paid Growth/Elite unlock) is a deliberate
 * future refinement — this pass shows the panel to any owner so the
 * feature is usable and testable without a live subscription.
 */
#[Layout('layouts.app', ['title' => 'Analytics'])]
class ClinicAnalytics extends Component
{
    public Clinic $clinic;

    #[Url]
    public string $range = '30'; // 30 | 90 | all (days)

    public function mount(Clinic $clinic): void
    {
        abort_unless(auth()->user()->can('clinics.manage'), 403);

        $this->clinic = $clinic;
    }

    protected function from(): ?Carbon
    {
        return $this->range === 'all' ? null : now()->subDays((int) $this->range);
    }

    /**
     * @return array<string, int> status => count
     */
    protected function countsByStatus(string $relation, string $dateColumn): array
    {
        $from = $this->from();

        return $this->clinic->{$relation}()
            ->when($from, fn ($q) => $q->where($dateColumn, '>=', $from))
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();
    }

    public function render(): View
    {
        $from = $this->from();

        $assignments = $this->countsByStatus('leadAssignments', 'assigned_at');
        $offers = $this->countsByStatus('offers', 'created_at');
        $appointments = $this->countsByStatus('appointments', 'created_at');

        $assigned = array_sum($assignments);
        $accepted = $assignments['accepted'] ?? 0;
        $responded = $accepted + ($assignments['declined'] ?? 0) + ($assignments['expired'] ?? 0);
        $offersSent = array_sum($offers);
        $offersAccepted = $offers['accepted'] ?? 0;

        // Completed cases + revenue (agreed price of cases completed in range).
        $completedCases = $this->clinic->treatmentCases()
            ->where('status', 'completed')
            ->when($from, fn ($q) => $q->where('completion_date', '>=', $from));
        $completedCount = (clone $completedCases)->count();
        $revenue = (int) (clone $completedCases)->sum('agreed_price');

        // Commission totals by status (amount in minor units), all-time —
        // money owed isn't windowed, it's a running balance.
        $commissions = $this->clinic->commissions()
            ->selectRaw('status, sum(amount) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        // Average first-response time (assigned -> responded), in hours.
        $responseRows = $this->clinic->leadAssignments()
            ->whereNotNull('responded_at')
            ->when($from, fn ($q) => $q->where('assigned_at', '>=', $from))
            ->get(['assigned_at', 'responded_at']);
        $avgResponseHours = $responseRows->isEmpty()
            ? null
            : round($responseRows->avg(fn ($a) => $a->assigned_at->diffInMinutes($a->responded_at)) / 60, 1);

        return view('livewire.clinic.clinic-analytics', [
            'kpis' => [
                'leads' => $assigned,
                'acceptanceRate' => $responded > 0 ? round($accepted / $responded * 100) : null,
                'offerRate' => $offersSent > 0 ? round($offersAccepted / $offersSent * 100) : null,
                'completedCases' => $completedCount,
                'revenue' => $revenue,
                'avgResponseHours' => $avgResponseHours,
            ],
            'funnel' => [
                ['label' => __('Leads received'), 'value' => $assigned],
                ['label' => __('Accepted'), 'value' => $accepted],
                ['label' => __('Offers sent'), 'value' => $offersSent],
                ['label' => __('Cases completed'), 'value' => $completedCount],
            ],
            'offers' => $offers,
            'appointments' => $appointments,
            'commissions' => $commissions,
            'reviews' => [
                'count' => $this->clinic->rating_count,
                'avg' => $this->clinic->rating_avg,
            ],
            'currency' => $this->clinic->commissions()->value('currency') ?? 'EUR',
        ]);
    }
}
