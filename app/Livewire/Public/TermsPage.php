<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §13 and docs/01-product-strategy.md §1
 * (Clinicest is a neutral broker, not a treatment provider) — that
 * distinction is the legal spine of these terms. Draft, same caveat as
 * PrivacyPolicyPage.
 */
#[Layout('layouts.public')]
class TermsPage extends Component
{
    public function render(): View
    {
        return view('livewire.public.terms-page');
    }
}
