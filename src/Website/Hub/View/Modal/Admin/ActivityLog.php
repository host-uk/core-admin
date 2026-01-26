<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

/**
 * Activity log viewer component.
 *
 * Displays paginated activity log for the current workspace.
 */
#[Title('Activity Log')]
#[Layout('hub::admin.layouts.app')]
class ActivityLog extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $logName = '';

    #[Url]
    public string $event = '';

    /**
     * Get available log names for filtering.
     */
    #[Computed]
    public function logNames(): array
    {
        return Activity::query()
            ->distinct()
            ->pluck('log_name')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get available events for filtering.
     */
    #[Computed]
    public function events(): array
    {
        return Activity::query()
            ->distinct()
            ->pluck('event')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get paginated activity records.
     */
    #[Computed]
    public function activities(): LengthAwarePaginator
    {
        $user = auth()->user();
        $workspace = $user?->defaultHostWorkspace();

        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest();

        // Filter by workspace members if workspace exists
        if ($workspace) {
            $memberIds = $workspace->users->pluck('id');
            $query->whereIn('causer_id', $memberIds);
        }

        // Filter by log name
        if ($this->logName) {
            $query->where('log_name', $this->logName);
        }

        // Filter by event
        if ($this->event) {
            $query->where('event', $this->event);
        }

        // Search in description
        if ($this->search) {
            $query->where('description', 'like', "%{$this->search}%");
        }

        return $query->paginate(20);
    }

    /**
     * Clear all filters.
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->logName = '';
        $this->event = '';
        $this->resetPage();
    }

    #[Computed]
    public function logNameOptions(): array
    {
        $options = ['' => 'All logs'];
        foreach ($this->logNames as $name) {
            $options[$name] = Str::title($name);
        }

        return $options;
    }

    #[Computed]
    public function eventOptions(): array
    {
        $options = ['' => 'All events'];
        foreach ($this->events as $eventName) {
            $options[$eventName] = Str::title($eventName);
        }

        return $options;
    }

    #[Computed]
    public function activityItems(): array
    {
        return $this->activities->map(function ($activity) {
            $item = [
                'description' => $activity->description,
                'event' => $activity->event ?? 'activity',
                'timestamp' => $activity->created_at,
            ];

            // Actor
            if ($activity->causer) {
                $item['actor'] = [
                    'name' => $activity->causer->name ?? 'User',
                    'initials' => substr($activity->causer->name ?? 'U', 0, 1),
                ];
            }

            // Subject
            if ($activity->subject) {
                $item['subject'] = [
                    'type' => class_basename($activity->subject_type),
                    'name' => $activity->subject->name
                        ?? $activity->subject->title
                        ?? $activity->subject->url
                        ?? (string) $activity->subject_id,
                ];
            }

            // Changes diff
            if ($activity->properties->has('old') && $activity->properties->has('new')) {
                $item['changes'] = [
                    'old' => $activity->properties['old'],
                    'new' => $activity->properties['new'],
                ];
            }

            return $item;
        })->all();
    }

    public function render()
    {
        return view('hub::admin.activity-log')
            ->layout('hub::admin.layouts.app', ['title' => 'Activity Log']);
    }
}
