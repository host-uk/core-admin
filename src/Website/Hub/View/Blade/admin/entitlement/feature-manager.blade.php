<admin:module title="Features" subtitle="Manage entitlement features">
    <x-slot:actions>
        <core:button wire:click="openCreate" icon="plus">New Feature</core:button>
    </x-slot:actions>

    <admin:flash />

    <admin:manager-table
        :columns="$this->tableColumns"
        :rows="$this->tableRows"
        :pagination="$this->features"
        empty="No features found. Create your first feature to get started."
        emptyIcon="puzzle-piece"
    />

    {{-- Create/Edit Feature Modal --}}
    <core:modal wire:model="showModal" class="max-w-xl">
        <core:heading size="lg">
            {{ $editingId ? 'Edit Feature' : 'Create Feature' }}
        </core:heading>

        <form wire:submit="save" class="mt-4 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <core:input wire:model="code" label="Code" placeholder="social.posts.scheduled" required />
                <core:input wire:model="name" label="Name" placeholder="Scheduled Posts" required />
            </div>

            <core:textarea wire:model="description" label="Description" rows="2" />

            <div class="grid grid-cols-2 gap-4">
                <core:select wire:model="category" label="Category">
                    <option value="">Select category...</option>
                    @foreach ($this->categories as $cat)
                        <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                    @endforeach
                    <option value="__new">+ New category</option>
                </core:select>

                <core:input wire:model="sort_order" label="Sort Order" type="number" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <core:select wire:model="type" label="Type">
                    <option value="boolean">Boolean (on/off)</option>
                    <option value="limit">Limit (numeric)</option>
                    <option value="unlimited">Unlimited</option>
                </core:select>

                <core:select wire:model="reset_type" label="Reset Type">
                    <option value="none">Never resets</option>
                    <option value="monthly">Monthly (billing cycle)</option>
                    <option value="rolling">Rolling window</option>
                </core:select>
            </div>

            @if ($reset_type === 'rolling')
                <core:input wire:model="rolling_window_days" label="Rolling Window (days)" type="number" placeholder="30" />
            @endif

            <core:select wire:model="parent_feature_id" label="Parent Feature (for global pools)">
                <option value="">No parent (standalone)</option>
                @foreach ($this->parentFeatures as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->code }})</option>
                @endforeach
            </core:select>

            <core:checkbox wire:model="is_active" label="Active" />

            <div class="flex justify-end gap-2 pt-4">
                <core:button variant="ghost" wire:click="closeModal">Cancel</core:button>
                <core:button type="submit" variant="primary">
                    {{ $editingId ? 'Update' : 'Create' }}
                </core:button>
            </div>
        </form>
    </core:modal>
</admin:module>
