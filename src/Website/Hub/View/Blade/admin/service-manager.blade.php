<admin:module title="Services" subtitle="Manage platform services and their configuration">
    <x-slot:actions>
        <core:button wire:click="syncFromModules" icon="rotate" variant="ghost">
            Sync from Modules
        </core:button>
    </x-slot:actions>

    <admin:flash />

    <admin:manager-table
        :columns="$this->tableColumns"
        :rows="$this->tableRows"
        empty="No services found. Run the sync to import services from modules."
        emptyIcon="cube"
    />

    {{-- Edit Service Modal --}}
    <core:modal wire:model="showModal" class="max-w-2xl">
        <core:heading size="lg">Edit Service</core:heading>

        <form wire:submit="save" class="mt-4 space-y-4">
            {{-- Read-only section --}}
            <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800/50 p-4 space-y-3">
                <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Module Information (read-only)</div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-zinc-500">Code</div>
                        <code class="text-sm font-mono">{{ $code }}</code>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500">Module</div>
                        <code class="text-sm font-mono">{{ $module }}</code>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500">Entitlement</div>
                        <code class="text-sm font-mono">{{ $entitlement_code ?: '-' }}</code>
                    </div>
                </div>
            </div>

            {{-- Editable fields --}}
            <div class="grid grid-cols-2 gap-4">
                <core:input wire:model="name" label="Display Name" placeholder="Bio" required />
                <core:input wire:model="tagline" label="Tagline" placeholder="Link-in-bio pages" />
            </div>

            <core:textarea wire:model="description" label="Description" rows="3" placeholder="Marketing description for the service catalogue..." />

            <div class="grid grid-cols-3 gap-4">
                <core:input wire:model="icon" label="Icon" placeholder="link" description="Font Awesome icon name" />
                <core:input wire:model="color" label="Colour" placeholder="pink" description="Tailwind colour name" />
                <core:input wire:model="sort_order" label="Sort Order" type="number" />
            </div>

            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-3">Marketing Configuration</div>
                <div class="grid grid-cols-2 gap-4">
                    <core:input wire:model="marketing_domain" label="Marketing Domain" placeholder="lthn.test" description="Domain for marketing site" />
                    <core:input wire:model="docs_url" label="Documentation URL" placeholder="https://docs.host.uk.com/bio" />
                </div>
                <core:input wire:model="marketing_url" label="Marketing URL Override" placeholder="https://lthn.test" description="Overrides auto-generated URL from domain" class="mt-4" />
            </div>

            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-3">Visibility</div>
                <div class="grid grid-cols-3 gap-4">
                    <core:checkbox wire:model="is_enabled" label="Enabled" description="Service is active" />
                    <core:checkbox wire:model="is_public" label="Public" description="Show in catalogue" />
                    <core:checkbox wire:model="is_featured" label="Featured" description="Highlight in marketing" />
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <core:button variant="ghost" wire:click="closeModal">Cancel</core:button>
                <core:button type="submit" variant="primary">Update Service</core:button>
            </div>
        </form>
    </core:modal>
</admin:module>
