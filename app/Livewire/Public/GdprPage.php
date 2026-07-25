<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §13. Focused specifically on GDPR rights and
 * how to exercise them, distinct from the broader PrivacyPolicyPage
 * (what/why data is collected). Draft, same caveat as PrivacyPolicyPage.
 */
#[Layout('layouts.public')]
class GdprPage extends Component
{
    public function render(): View
    {
        return view('livewire.public.gdpr-page');
    }
}
