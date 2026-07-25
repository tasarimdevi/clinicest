<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithTranslations;
use App\Models\Faq;
use App\Models\Treatment;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Create/edit a FAQ. Attaching it to a treatment (optional) makes it a
 * treatment-page FAQ; leaving it unattached makes it a global FAQ shown on
 * the FAQ hub. Only Treatment is offered as a scope here — the other
 * faqable target (Post) is a rarer case left out of this pass.
 */
#[Layout('layouts.app', ['title' => 'FAQ'])]
class FaqForm extends Component
{
    use WithTranslations;

    public ?Faq $faq = null;

    /** @var array<string, string> */
    public array $question = [];

    /** @var array<string, string> */
    public array $answer = [];

    public string $category = '';

    public ?int $treatment_id = null;

    public int $sort = 0;

    public string $status = 'published';

    protected function translatableFields(): array
    {
        return ['question' => 'question', 'answer' => 'answer'];
    }

    public function mount(mixed $faq = null): void
    {
        $model = match (true) {
            $faq instanceof Faq => $faq,
            $faq !== null => Faq::findOrFail($faq),
            default => null,
        };

        $this->authorize($model ? 'update' : 'create', $model ?? Faq::class);

        $this->emptyTranslations(['question', 'answer']);

        if ($model) {
            $this->faq = $model;
            $this->fillTranslations($model);
            $this->category = $model->category ?? '';
            $this->treatment_id = $model->faqable_type === Treatment::class ? $model->faqable_id : null;
            $this->sort = (int) $model->sort;
            $this->status = $model->status ?? 'published';
        }
    }

    protected function rules(): array
    {
        return [
            ...$this->translationRules([
                'question' => ['required' => true, 'max' => 500],
                'answer' => ['required' => true, 'max' => null],
            ]),
            'category' => ['nullable', 'string', 'max:255'],
            'treatment_id' => ['nullable', Rule::exists(Treatment::class, 'id')],
            'sort' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ];
    }

    public function save(): void
    {
        $this->authorize($this->faq ? 'update' : 'create', $this->faq ?? Faq::class);

        $validated = $this->validate();

        // Publishing is gated separately from editing (content.publish),
        // same split as posts/treatments — a content editor can't take a
        // FAQ live via the status field.
        if ($validated['status'] === 'published' && ! auth()->user()->can('content.publish')) {
            abort_unless($this->faq && $this->faq->status === 'published', 403, 'You cannot publish content.');
        }

        $model = $this->faq ?? new Faq;
        $model->fill([
            'category' => $validated['category'] ?: null,
            'faqable_type' => $validated['treatment_id'] ? Treatment::class : null,
            'faqable_id' => $validated['treatment_id'] ?: null,
            'sort' => $validated['sort'],
            'status' => $validated['status'],
        ]);
        $this->applyTranslations($model);
        $model->save();
        $this->faq = $model;

        $this->redirect(route('admin.faqs.edit', $this->faq), navigate: false);
    }

    public function render(): View
    {
        return view('livewire.admin.faq-form', [
            'treatments' => Treatment::orderBy('sort')->get(),
        ]);
    }
}
