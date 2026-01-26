<admin:module title="Waitlist" subtitle="Manage signups and invitations">
    <x-slot:actions>
        <core:button wire:click="export" icon="arrow-down-tray" variant="ghost">Export CSV</core:button>
        @if (count($selected) > 0)
            <core:button wire:click="sendBulkInvites" icon="paper-airplane" variant="primary">
                Invite Selected ({{ count($selected) }})
            </core:button>
        @endif
    </x-slot:actions>

    <admin:flash />

    {{-- Stats Cards --}}
    <admin:stats cols="4" class="mb-6">
        <admin:stat-card label="Total signups" :value="number_format($totalCount)" />
        <admin:stat-card label="Pending invite" :value="number_format($pendingCount)" color="amber" />
        <admin:stat-card label="Invited" :value="number_format($invitedCount)" color="blue" />
        <admin:stat-card label="Converted" :value="number_format($convertedCount)" color="green" />
    </admin:stats>

    <admin:filter-bar cols="4">
        <admin:search model="search" placeholder="Search emails or names..." />
        <admin:filter model="statusFilter" :options="$this->statusOptions" placeholder="All entries" />
        <admin:filter model="interestFilter" :options="$this->interests" placeholder="All interests" />
        <div class="flex items-center">
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <input type="checkbox" wire:model.live="selectAll" class="rounded">
                Select all
            </label>
        </div>
    </admin:filter-bar>

    <admin:manager-table
        :columns="$this->tableColumns"
        :rows="$this->tableRows"
        :pagination="$this->entries"
        empty="No waitlist entries found."
        emptyIcon="users"
    />
</admin:module>
