<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Dashboard - Simple hub landing page.
 */
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(): View
    {
        return view('hub::admin.dashboard')
            ->layout('hub::admin.layouts.app', ['title' => 'Dashboard']);
    }
}
