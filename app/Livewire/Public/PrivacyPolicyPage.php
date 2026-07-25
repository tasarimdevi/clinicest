<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/04-wireframes.md §13. Content is grounded in what the app
 * actually collects/does today (GetQuote fields, App\Models\Consent
 * tracking, cross-border sharing with Turkish clinics) rather than
 * generic boilerplate — but it is still a draft: no registered legal
 * entity/address exists for this project yet (see the notice banner in
 * the view), so it isn't wired as the platform's actual binding policy
 * until a real business reviews and completes it.
 */
#[Layout('layouts.public')]
class PrivacyPolicyPage extends Component
{
    public function render(): View
    {
        return view('livewire.public.privacy-policy-page');
    }
}
