<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Generic stand-in for public pages not yet built out (see docs/04-wireframes.md
 * for each page's real spec). Keeps every route resolvable to a real Livewire
 * component from day one instead of dead links, per the documented
 * one-Livewire-component-per-page pattern (docs/08-laravel-architecture.md §2).
 */
#[Layout('layouts.public')]
class PlaceholderPage extends Component
{
    public string $title;

    public function mount(string $title): void
    {
        $this->title = $title;
    }

    public function render()
    {
        return view('livewire.public.placeholder-page');
    }
}
