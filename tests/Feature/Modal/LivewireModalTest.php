<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Livewire;

/**
 * Tests for Livewire modal system.
 *
 * These tests verify modal opening/closing behaviour, event handling,
 * data passing, validation, and nested modal support. The tests use
 * test double components to isolate modal behaviour from specific
 * application logic.
 */

// =============================================================================
// Test Double Components
// =============================================================================

/**
 * Basic modal component for testing open/close behaviour.
 */
class BasicModalComponent extends Component
{
    public bool $showModal = false;

    public string $title = '';

    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset('title');
    }

    public function render(): string
    {
        return <<<'HTML'
            <div>
                <button wire:click="openModal">Open</button>
                @if($showModal)
                    <div class="modal">
                        <h1>{{ $title }}</h1>
                        <button wire:click="closeModal">Close</button>
                    </div>
                @endif
            </div>
        HTML;
    }
}

/**
 * Modal component for testing event dispatch and listening.
 */
class EventModalComponent extends Component
{
    public bool $open = false;

    public array $receivedEvents = [];

    #[On('open-modal')]
    public function handleOpenModal(string $source = ''): void
    {
        $this->open = true;
        $this->receivedEvents[] = ['type' => 'open-modal', 'source' => $source];
    }

    #[On('close-modal')]
    public function handleCloseModal(): void
    {
        $this->open = false;
        $this->receivedEvents[] = ['type' => 'close-modal'];
    }

    public function closeAndDispatch(): void
    {
        $this->open = false;
        $this->dispatch('modal-closed', result: 'success');
    }

    public function dispatchToParent(): void
    {
        $this->dispatch('data-updated', data: ['key' => 'value']);
    }

    public function render(): string
    {
        return <<<'HTML'
            <div>
                <span>{{ $open ? 'Open' : 'Closed' }}</span>
                <span>Events: {{ count($receivedEvents) }}</span>
            </div>
        HTML;
    }
}

/**
 * Modal component for testing data passing.
 */
class DataModalComponent extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public array $items = [];

    public function openWithData(int $id, string $name, string $email): void
    {
        $this->editingId = $id;
        $this->name = $name;
        $this->email = $email;
        $this->showModal = true;
    }

    public function openWithItems(array $items): void
    {
        $this->items = $items;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->items = [];
    }

    #[Computed]
    public function itemCount(): int
    {
        return count($this->items);
    }

    public function render(): string
    {
        return <<<'HTML'
            <div>
                @if($showModal)
                    <div class="modal">
                        <span>ID: {{ $editingId }}</span>
                        <span>Name: {{ $name }}</span>
                        <span>Email: {{ $email }}</span>
                        <span>Items: {{ $this->itemCount }}</span>
                    </div>
                @endif
            </div>
        HTML;
    }
}

/**
 * Modal component for testing validation.
 */
class ValidationModalComponent extends Component
{
    public bool $showModal = false;

    public string $name = '';

    public string $email = '';

    public string $description = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Simulate successful save
        $this->dispatch('saved', name: $this->name);
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['name', 'email', 'description']);
        $this->resetErrorBag();
    }

    public function render(): string
    {
        return <<<'HTML'
            <div>
                @if($showModal)
                    <form wire:submit="save">
                        <input wire:model="name" />
                        <input wire:model="email" />
                        <textarea wire:model="description"></textarea>
                        @error('name') <span class="error">{{ $message }}</span> @enderror
                        @error('email') <span class="error">{{ $message }}</span> @enderror
                    </form>
                @endif
            </div>
        HTML;
    }
}

/**
 * Modal component for testing nested/stacked modals.
 */
class NestedModalComponent extends Component
{
    public bool $primaryModal = false;

    public bool $secondaryModal = false;

    public bool $tertiaryModal = false;

    public string $activeLevel = '';

    public function openPrimary(): void
    {
        $this->primaryModal = true;
        $this->activeLevel = 'primary';
    }

    public function openSecondary(): void
    {
        $this->secondaryModal = true;
        $this->activeLevel = 'secondary';
    }

    public function openTertiary(): void
    {
        $this->tertiaryModal = true;
        $this->activeLevel = 'tertiary';
    }

    public function closePrimary(): void
    {
        $this->primaryModal = false;
        $this->activeLevel = '';
    }

    public function closeSecondary(): void
    {
        $this->secondaryModal = false;
        $this->activeLevel = $this->primaryModal ? 'primary' : '';
    }

    public function closeTertiary(): void
    {
        $this->tertiaryModal = false;
        $this->activeLevel = $this->secondaryModal ? 'secondary' : ($this->primaryModal ? 'primary' : '');
    }

    public function closeAll(): void
    {
        $this->primaryModal = false;
        $this->secondaryModal = false;
        $this->tertiaryModal = false;
        $this->activeLevel = '';
    }

    #[Computed]
    public function openModalsCount(): int
    {
        return ($this->primaryModal ? 1 : 0)
            + ($this->secondaryModal ? 1 : 0)
            + ($this->tertiaryModal ? 1 : 0);
    }

    public function render(): string
    {
        return <<<'HTML'
            <div>
                <span>Active: {{ $activeLevel }}</span>
                <span>Open: {{ $this->openModalsCount }}</span>
            </div>
        HTML;
    }
}

/**
 * Modal component for testing lifecycle and state management.
 */
class LifecycleModalComponent extends Component
{
    public bool $showModal = false;

    public int $mountCount = 0;

    public int $updateCount = 0;

    public string $state = '';

    public function mount(): void
    {
        $this->mountCount++;
    }

    public function updated(): void
    {
        $this->updateCount++;
    }

    public function openModal(): void
    {
        $this->showModal = true;
        $this->state = 'opened';
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->state = 'closed';
    }

    public function toggleModal(): void
    {
        $this->showModal = ! $this->showModal;
        $this->state = $this->showModal ? 'toggled-open' : 'toggled-closed';
    }

    public function render(): string
    {
        return <<<'HTML'
            <div>
                <span>Mount: {{ $mountCount }}</span>
                <span>Updates: {{ $updateCount }}</span>
                <span>State: {{ $state }}</span>
            </div>
        HTML;
    }
}

// =============================================================================
// Modal Opening/Closing Tests
// =============================================================================

describe('Modal opening and closing', function () {
    it('opens modal when openModal is called', function () {
        Livewire::test(BasicModalComponent::class)
            ->assertSet('showModal', false)
            ->call('openModal')
            ->assertSet('showModal', true)
            ->assertSee('Close');
    });

    it('closes modal when closeModal is called', function () {
        Livewire::test(BasicModalComponent::class)
            ->set('showModal', true)
            ->call('closeModal')
            ->assertSet('showModal', false);
    });

    it('resets state when modal closes', function () {
        Livewire::test(BasicModalComponent::class)
            ->set('showModal', true)
            ->set('title', 'Test Title')
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertSet('title', '');
    });

    it('can toggle modal state', function () {
        Livewire::test(LifecycleModalComponent::class)
            ->assertSet('showModal', false)
            ->call('toggleModal')
            ->assertSet('showModal', true)
            ->assertSet('state', 'toggled-open')
            ->call('toggleModal')
            ->assertSet('showModal', false)
            ->assertSet('state', 'toggled-closed');
    });

    it('can set modal state directly via wire:model', function () {
        Livewire::test(BasicModalComponent::class)
            ->set('showModal', true)
            ->assertSet('showModal', true)
            ->set('showModal', false)
            ->assertSet('showModal', false);
    });

    it('renders modal content only when open', function () {
        Livewire::test(BasicModalComponent::class)
            ->assertDontSee('Close') // Modal closed, button not visible
            ->call('openModal')
            ->assertSee('Close'); // Modal open, button visible
    });
});

// =============================================================================
// Modal Event Tests
// =============================================================================

describe('Modal events', function () {
    it('responds to open-modal event', function () {
        Livewire::test(EventModalComponent::class)
            ->assertSet('open', false)
            ->dispatch('open-modal', source: 'button-click')
            ->assertSet('open', true)
            ->assertSet('receivedEvents', fn ($events) => count($events) === 1
                && $events[0]['type'] === 'open-modal'
                && $events[0]['source'] === 'button-click'
            );
    });

    it('responds to close-modal event', function () {
        Livewire::test(EventModalComponent::class)
            ->set('open', true)
            ->dispatch('close-modal')
            ->assertSet('open', false)
            ->assertSet('receivedEvents', fn ($events) => count($events) === 1 && $events[0]['type'] === 'close-modal');
    });

    it('dispatches event when closing modal', function () {
        Livewire::test(EventModalComponent::class)
            ->set('open', true)
            ->call('closeAndDispatch')
            ->assertSet('open', false)
            ->assertDispatched('modal-closed', result: 'success');
    });

    it('dispatches data events to parent components', function () {
        Livewire::test(EventModalComponent::class)
            ->call('dispatchToParent')
            ->assertDispatched('data-updated', data: ['key' => 'value']);
    });

    it('accumulates multiple events', function () {
        Livewire::test(EventModalComponent::class)
            ->dispatch('open-modal', source: 'first')
            ->dispatch('close-modal')
            ->dispatch('open-modal', source: 'second')
            ->assertSet('receivedEvents', fn ($events) => count($events) === 3);
    });

    it('handles events with default parameters', function () {
        Livewire::test(EventModalComponent::class)
            ->dispatch('open-modal') // No source parameter
            ->assertSet('open', true)
            ->assertSet('receivedEvents', fn ($events) => $events[0]['source'] === '');
    });
});

// =============================================================================
// Modal Data Passing Tests
// =============================================================================

describe('Modal data passing', function () {
    it('receives scalar data when opening modal', function () {
        Livewire::test(DataModalComponent::class)
            ->call('openWithData', 42, 'John Doe', 'john@example.com')
            ->assertSet('showModal', true)
            ->assertSet('editingId', 42)
            ->assertSet('name', 'John Doe')
            ->assertSet('email', 'john@example.com');
    });

    it('receives array data when opening modal', function () {
        $items = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
            ['id' => 3, 'name' => 'Item 3'],
        ];

        Livewire::test(DataModalComponent::class)
            ->call('openWithItems', $items)
            ->assertSet('showModal', true)
            ->assertSet('items', $items)
            ->assertSet(fn ($component) => $component->itemCount === 3);
    });

    it('resets data when modal closes', function () {
        Livewire::test(DataModalComponent::class)
            ->call('openWithData', 42, 'John Doe', 'john@example.com')
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertSet('editingId', null)
            ->assertSet('name', '')
            ->assertSet('email', '')
            ->assertSet('items', []);
    });

    it('preserves data while modal is open', function () {
        Livewire::test(DataModalComponent::class)
            ->call('openWithData', 42, 'John Doe', 'john@example.com')
            ->set('name', 'Jane Doe')
            ->assertSet('editingId', 42) // Unchanged
            ->assertSet('name', 'Jane Doe') // Updated
            ->assertSet('email', 'john@example.com'); // Unchanged
    });

    it('renders data in modal view', function () {
        Livewire::test(DataModalComponent::class)
            ->call('openWithData', 42, 'John Doe', 'john@example.com')
            ->assertSee('ID: 42')
            ->assertSee('Name: John Doe')
            ->assertSee('Email: john@example.com');
    });

    it('handles empty array data', function () {
        Livewire::test(DataModalComponent::class)
            ->call('openWithItems', [])
            ->assertSet('showModal', true)
            ->assertSet('items', [])
            ->assertSee('Items: 0');
    });
});

// =============================================================================
// Modal Validation Tests
// =============================================================================

describe('Modal validation', function () {
    it('validates required fields', function () {
        Livewire::test(ValidationModalComponent::class)
            ->set('showModal', true)
            ->set('name', '')
            ->set('email', '')
            ->call('save')
            ->assertHasErrors(['name', 'email']);
    });

    it('validates email format', function () {
        Livewire::test(ValidationModalComponent::class)
            ->set('showModal', true)
            ->set('name', 'Valid Name')
            ->set('email', 'invalid-email')
            ->call('save')
            ->assertHasErrors(['email'])
            ->assertHasNoErrors(['name']);
    });

    it('validates minimum length', function () {
        Livewire::test(ValidationModalComponent::class)
            ->set('showModal', true)
            ->set('name', 'AB') // Too short (min:3)
            ->set('email', 'valid@email.com')
            ->call('save')
            ->assertHasErrors(['name']);
    });

    it('passes validation with valid data', function () {
        Livewire::test(ValidationModalComponent::class)
            ->set('showModal', true)
            ->set('name', 'Valid Name')
            ->set('email', 'valid@email.com')
            ->set('description', 'Optional description')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('saved', name: 'Valid Name')
            ->assertSet('showModal', false);
    });

    it('clears validation errors when modal closes', function () {
        Livewire::test(ValidationModalComponent::class)
            ->set('showModal', true)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name'])
            ->call('closeModal')
            ->assertHasNoErrors();
    });

    it('resets form data when modal closes after validation errors', function () {
        Livewire::test(ValidationModalComponent::class)
            ->set('showModal', true)
            ->set('name', 'Invalid')
            ->set('email', 'bad-email')
            ->call('save')
            ->assertHasErrors(['email'])
            ->call('closeModal')
            ->assertSet('name', '')
            ->assertSet('email', '')
            ->assertSet('showModal', false);
    });

    it('allows nullable fields', function () {
        Livewire::test(ValidationModalComponent::class)
            ->set('showModal', true)
            ->set('name', 'Valid Name')
            ->set('email', 'valid@email.com')
            ->set('description', '') // Nullable field
            ->call('save')
            ->assertHasNoErrors(['description']);
    });
});

// =============================================================================
// Nested Modal Tests
// =============================================================================

describe('Nested modals', function () {
    it('can open primary modal', function () {
        Livewire::test(NestedModalComponent::class)
            ->call('openPrimary')
            ->assertSet('primaryModal', true)
            ->assertSet('activeLevel', 'primary')
            ->assertSet(fn ($component) => $component->openModalsCount === 1);
    });

    it('can open secondary modal on top of primary', function () {
        Livewire::test(NestedModalComponent::class)
            ->call('openPrimary')
            ->call('openSecondary')
            ->assertSet('primaryModal', true)
            ->assertSet('secondaryModal', true)
            ->assertSet('activeLevel', 'secondary')
            ->assertSet(fn ($component) => $component->openModalsCount === 2);
    });

    it('can open tertiary modal creating three-level stack', function () {
        Livewire::test(NestedModalComponent::class)
            ->call('openPrimary')
            ->call('openSecondary')
            ->call('openTertiary')
            ->assertSet('primaryModal', true)
            ->assertSet('secondaryModal', true)
            ->assertSet('tertiaryModal', true)
            ->assertSet('activeLevel', 'tertiary')
            ->assertSet(fn ($component) => $component->openModalsCount === 3);
    });

    it('closes tertiary modal and returns to secondary', function () {
        Livewire::test(NestedModalComponent::class)
            ->call('openPrimary')
            ->call('openSecondary')
            ->call('openTertiary')
            ->call('closeTertiary')
            ->assertSet('tertiaryModal', false)
            ->assertSet('secondaryModal', true)
            ->assertSet('primaryModal', true)
            ->assertSet('activeLevel', 'secondary');
    });

    it('closes secondary modal and returns to primary', function () {
        Livewire::test(NestedModalComponent::class)
            ->call('openPrimary')
            ->call('openSecondary')
            ->call('closeSecondary')
            ->assertSet('secondaryModal', false)
            ->assertSet('primaryModal', true)
            ->assertSet('activeLevel', 'primary');
    });

    it('can close all modals at once', function () {
        Livewire::test(NestedModalComponent::class)
            ->call('openPrimary')
            ->call('openSecondary')
            ->call('openTertiary')
            ->call('closeAll')
            ->assertSet('primaryModal', false)
            ->assertSet('secondaryModal', false)
            ->assertSet('tertiaryModal', false)
            ->assertSet('activeLevel', '')
            ->assertSet(fn ($component) => $component->openModalsCount === 0);
    });

    it('tracks active level correctly when closing out of order', function () {
        Livewire::test(NestedModalComponent::class)
            ->call('openPrimary')
            ->call('openSecondary')
            ->call('openTertiary')
            ->call('closeSecondary') // Close middle modal
            ->assertSet('activeLevel', 'primary')
            ->assertSet('primaryModal', true)
            ->assertSet('secondaryModal', false)
            ->assertSet('tertiaryModal', false);
    });
});

// =============================================================================
// Modal Lifecycle Tests
// =============================================================================

describe('Modal lifecycle', function () {
    it('mounts once on component creation', function () {
        Livewire::test(LifecycleModalComponent::class)
            ->assertSet('mountCount', 1);
    });

    it('tracks updates when modal state changes', function () {
        Livewire::test(LifecycleModalComponent::class)
            ->assertSet('updateCount', 0)
            ->call('openModal')
            ->assertSet('updateCount', fn ($count) => $count > 0);
    });

    it('maintains state across multiple operations', function () {
        Livewire::test(LifecycleModalComponent::class)
            ->call('openModal')
            ->assertSet('state', 'opened')
            ->call('closeModal')
            ->assertSet('state', 'closed')
            ->call('openModal')
            ->assertSet('state', 'opened');
    });

    it('mount is not called again when modal opens', function () {
        Livewire::test(LifecycleModalComponent::class)
            ->assertSet('mountCount', 1)
            ->call('openModal')
            ->assertSet('mountCount', 1) // Still 1
            ->call('closeModal')
            ->assertSet('mountCount', 1); // Still 1
    });
});

// =============================================================================
// Edge Cases and Boundary Tests
// =============================================================================

describe('Edge cases', function () {
    it('handles rapid open/close cycles', function () {
        $component = Livewire::test(BasicModalComponent::class);

        for ($i = 0; $i < 10; $i++) {
            $component->call('openModal')
                ->assertSet('showModal', true)
                ->call('closeModal')
                ->assertSet('showModal', false);
        }
    });

    it('handles multiple event dispatches in sequence', function () {
        Livewire::test(EventModalComponent::class)
            ->dispatch('open-modal')
            ->dispatch('close-modal')
            ->dispatch('open-modal')
            ->dispatch('close-modal')
            ->assertSet('receivedEvents', fn ($events) => count($events) === 4);
    });

    it('preserves computed properties when modal is open', function () {
        $items = [['id' => 1], ['id' => 2]];

        Livewire::test(DataModalComponent::class)
            ->call('openWithItems', $items)
            ->assertSet(fn ($component) => $component->itemCount === 2)
            ->set('items', [...$items, ['id' => 3]])
            ->assertSet(fn ($component) => $component->itemCount === 3);
    });

    it('handles closing already closed modal gracefully', function () {
        Livewire::test(BasicModalComponent::class)
            ->assertSet('showModal', false)
            ->call('closeModal') // Already closed
            ->assertSet('showModal', false); // Still closed, no error
    });

    it('handles opening already open modal gracefully', function () {
        Livewire::test(BasicModalComponent::class)
            ->call('openModal')
            ->assertSet('showModal', true)
            ->call('openModal') // Already open
            ->assertSet('showModal', true); // Still open, no error
    });

    it('handles special characters in data', function () {
        Livewire::test(DataModalComponent::class)
            ->call('openWithData', 1, 'John "Jack" O\'Brien', 'test+special@example.co.uk')
            ->assertSet('name', 'John "Jack" O\'Brien')
            ->assertSet('email', 'test+special@example.co.uk');
    });

    it('handles unicode in data', function () {
        Livewire::test(DataModalComponent::class)
            ->call('openWithData', 1, "\xC3\x89mile Zola", "emile@caf\xC3\xA9.fr")
            ->assertSet('name', "\xC3\x89mile Zola")
            ->assertSet('email', "emile@caf\xC3\xA9.fr");
    });

    it('handles very long strings in data', function () {
        $longName = str_repeat('A', 1000);
        $longEmail = 'a'.str_repeat('b', 100).'@example.com';

        Livewire::test(DataModalComponent::class)
            ->call('openWithData', 1, $longName, $longEmail)
            ->assertSet('name', $longName)
            ->assertSet('email', $longEmail);
    });
});
