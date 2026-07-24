<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Actions\Leads\CreateLead;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\Lead;
use App\Models\Treatment;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The primary conversion funnel. See docs/02-information-architecture-ux.md §4
 * for the full 4-step flow spec (photos/x-ray upload, timeline, etc.) —
 * this is the first working slice: treatment + contact + consent -> Lead.
 *
 * Context pre-fill: arriving via ?treatment={id} (from a treatment page)
 * pre-selects that treatment; ?clinic={id} (from a clinic page) can't set
 * a direct clinic relationship — leads aren't assigned to a clinic until
 * an agent does so (docs/09-crm-admin-architecture.md §2) — so it's
 * reflected in the message instead, giving the agent honest context
 * without fabricating a schema relationship that doesn't exist.
 */
#[Layout('layouts.public')]
class GetQuote extends Component
{
    public ?int $primary_treatment_id = null;

    public ?int $country_id = null;

    public string $full_name = '';

    public string $email = '';

    public string $whatsapp = '';

    public string $message = '';

    public bool $consent = false;

    public bool $submitted = false;

    public function mount(): void
    {
        if ($treatmentId = request()->integer('treatment')) {
            if (Treatment::where('id', $treatmentId)->where('status', 'published')->exists()) {
                $this->primary_treatment_id = $treatmentId;
            }
        }

        if ($clinicId = request()->integer('clinic')) {
            if ($clinic = Clinic::where('id', $clinicId)->where('is_active', true)->first()) {
                $this->message = __('I\'m interested in a quote from :clinic.', [
                    'clinic' => $clinic->getTranslation('name', app()->getLocale()),
                ]);
            }
        }

        if ($countryId = request()->integer('country')) {
            if (Country::where('id', $countryId)->where('is_target', true)->exists()) {
                $this->country_id = $countryId;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'primary_treatment_id' => ['nullable', Rule::exists(Treatment::class, 'id')],
            'country_id' => ['nullable', Rule::exists(Country::class, 'id')],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
        ];
    }

    public function submit(CreateLead $createLead): void
    {
        $validated = $this->validate();

        /** @var Lead $lead */
        $lead = $createLead->handle([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'whatsapp' => $validated['whatsapp'] ?: null,
            'country_id' => $validated['country_id'],
            'primary_treatment_id' => $validated['primary_treatment_id'],
            'message' => $validated['message'] ?: null,
            'channel' => 'web',
            'locale' => app()->getLocale(),
            'source' => ['utm' => request()->only(['utm_source', 'utm_medium', 'utm_campaign'])],
            'consent' => [
                'granted' => $validated['consent'],
                'text_version' => 'v1',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
        ]);

        $this->submitted = true;
        $this->reset(['full_name', 'email', 'whatsapp', 'message', 'consent']);
    }

    public function render()
    {
        return view('livewire.public.get-quote', [
            'treatments' => Treatment::query()->where('status', 'published')->orderBy('sort')->get(),
            'countries' => Country::query()->where('is_target', true)->orderBy('name')->get(),
        ]);
    }
}
