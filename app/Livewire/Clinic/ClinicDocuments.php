<?php

declare(strict_types=1);

namespace App\Livewire\Clinic;

use App\Actions\Documents\UploadDocument;
use App\Enums\DocumentType;
use App\Models\Clinic;
use App\Models\Document;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * See docs/09-crm-admin-architecture.md §3 "Documents". One page for
 * both clinic-level documents (certificates, verification uploads —
 * lead_id left blank) and documents tied to a specific accepted patient
 * (treatment plans, x-rays — lead_id selected), rather than two nearly
 * identical pages, since docs bundles both under one feature.
 */
#[Layout('layouts.app', ['title' => 'Documents'])]
class ClinicDocuments extends Component
{
    use WithFileUploads;

    public Clinic $clinic;

    public string $type = 'certificate';

    public string $title = '';

    public ?int $lead_id = null;

    public $file = null;

    public function mount(Clinic $clinic): void
    {
        $this->authorize('viewAny', Document::class);

        $this->clinic = $clinic;
    }

    protected function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(DocumentType::class)],
            'title' => ['required', 'string', 'max:255'],
            'lead_id' => ['nullable', Rule::exists('leads', 'id')],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];
    }

    public function upload(UploadDocument $uploadDocument): void
    {
        $this->authorize('create', Document::class);

        $validated = $this->validate();

        $lead = null;

        if ($validated['lead_id']) {
            $lead = Lead::findOrFail($validated['lead_id']);

            abort_unless(
                $this->clinic->leadAssignments()->where('lead_id', $lead->id)->where('status', 'accepted')->exists(),
                403,
                'This lead has not been accepted by your clinic yet.'
            );
        }

        $uploadDocument->handle($this->clinic, $lead, $this->file, [
            'type' => $validated['type'],
            'title' => $validated['title'],
        ], auth()->user());

        $this->reset(['title', 'file', 'lead_id']);
    }

    public function delete(Document $document): void
    {
        abort_unless($document->clinic_id === $this->clinic->id, 403);

        $this->authorize('delete', $document);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();
    }

    public function render(): View
    {
        return view('livewire.clinic.clinic-documents', [
            'documents' => Document::where('clinic_id', $this->clinic->id)
                ->with(['lead', 'uploadedBy'])
                ->latest()
                ->get(),
            'types' => DocumentType::cases(),
            'acceptedLeads' => Lead::whereHas('assignments', fn ($q) => $q->where('clinic_id', $this->clinic->id)->where('status', 'accepted'))->get(),
        ]);
    }
}
