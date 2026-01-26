<admin:module title="Packages" subtitle="Manage entitlement packages">
    <x-slot:actions>
        <core:button wire:click="openCreate" icon="plus">New Package</core:button>
    </x-slot:actions>

    <admin:flash />

    <admin:manager-table
        :columns="$this->tableColumns"
        :rows="$this->tableRows"
        :pagination="$this->packages"
        empty="No packages found. Create your first package to get started."
        emptyIcon="cube"
    />

    {{-- Create/Edit Package Modal --}}
    <core:modal wire:model="showModal" class="max-w-xl">
        <core:heading size="lg">
            {{ $editingId ? 'Edit Package' : 'Create Package' }}
        </core:heading>

        <form wire:submit="save" class="mt-4 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <core:input wire:model="code" label="Code" placeholder="creator" required />
                <core:input wire:model="name" label="Name" placeholder="Creator Plan" required />
            </div>

            <core:textarea wire:model="description" label="Description" rows="2" />

            <div class="grid grid-cols-3 gap-4">
                <core:input wire:model="icon" label="Icon" placeholder="user" />
                <core:input wire:model="color" label="Colour" placeholder="blue" />
                <core:input wire:model="sort_order" label="Sort Order" type="number" />
            </div>

            <core:input wire:model="blesta_package_id" label="Blesta Package ID" placeholder="pkg_123" />

            <div class="grid grid-cols-2 gap-4">
                <core:checkbox wire:model="is_base_package" label="Base Package" description="Only one base package per workspace" />
                <core:checkbox wire:model="is_stackable" label="Stackable" description="Can be combined with other packages" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <core:checkbox wire:model="is_active" label="Active" />
                <core:checkbox wire:model="is_public" label="Public" description="Show on pricing page" />
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <core:button variant="ghost" wire:click="closeModal">Cancel</core:button>
                <core:button type="submit" variant="primary">
                    {{ $editingId ? 'Update' : 'Create' }}
                </core:button>
            </div>
        </form>
    </core:modal>

    {{-- Features Assignment Modal --}}
    <core:modal wire:model="showFeaturesModal" class="max-w-2xl">
        <core:heading size="lg">Assign Features</core:heading>

        <form wire:submit="saveFeatures" class="mt-4 space-y-6">
            @foreach ($this->features as $category => $categoryFeatures)
                <div>
                    <core:heading size="sm" class="mb-2 capitalize">{{ $category }}</core:heading>
                    <div class="space-y-2">
                        @foreach ($categoryFeatures as $feature)
                            <div class="flex items-center gap-4 p-2 rounded border border-gray-200 dark:border-gray-700">
                                <core:checkbox
                                    wire:click="toggleFeature({{ $feature->id }})"
                                    :checked="isset($selectedFeatures[$feature->id]['enabled']) && $selectedFeatures[$feature->id]['enabled']"
                                />
                                <div class="flex-1">
                                    <div class="font-medium">{{ $feature->name }}</div>
                                    <code class="text-xs text-gray-500">{{ $feature->code }}</code>
                                </div>
                                @if ($feature->type === 'limit')
                                    <core:input
                                        type="number"
                                        wire:model="selectedFeatures.{{ $feature->id }}.limit"
                                        placeholder="Limit"
                                        class="w-24"
                                        :disabled="!isset($selectedFeatures[$feature->id]['enabled']) || !$selectedFeatures[$feature->id]['enabled']"
                                    />
                                @elseif ($feature->type === 'unlimited')
                                    <core:badge color="purple">Unlimited</core:badge>
                                @else
                                    <core:badge color="gray">Boolean</core:badge>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end gap-2 pt-4">
                <core:button variant="ghost" wire:click="closeModal">Cancel</core:button>
                <core:button type="submit" variant="primary">Save Features</core:button>
            </div>
        </form>
    </core:modal>
</admin:module>
