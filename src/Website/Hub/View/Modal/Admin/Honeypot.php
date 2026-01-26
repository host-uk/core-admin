<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Mod\Hub\Models\HoneypotHit;
use Livewire\Component;
use Livewire\WithPagination;

class Honeypot extends Component
{
    use WithPagination;

    public string $search = '';

    public string $botFilter = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'botFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (! auth()->user()?->isHades()) {
            abort(403, 'Hades tier required.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function deleteOld(int $days = 30): void
    {
        HoneypotHit::where('created_at', '<', now()->subDays($days))->delete();
        session()->flash('message', "Deleted hits older than {$days} days.");
    }

    public function blockIp(string $ip): void
    {
        // This could integrate with a firewall or rate limiter
        // For now, just show a message
        session()->flash('message', "IP {$ip} flagged for review. Add to firewall manually.");
    }

    public function render()
    {
        $hits = HoneypotHit::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('ip_address', 'like', "%{$this->search}%")
                        ->orWhere('user_agent', 'like', "%{$this->search}%")
                        ->orWhere('bot_name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->botFilter !== '', function ($query) {
                $query->where('is_bot', $this->botFilter === '1');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(50);

        return view('hub::admin.honeypot', [
            'hits' => $hits,
            'stats' => HoneypotHit::getStats(),
        ])->layout('hub::admin.layouts.app', ['title' => 'Honeypot Monitor']);
    }
}
