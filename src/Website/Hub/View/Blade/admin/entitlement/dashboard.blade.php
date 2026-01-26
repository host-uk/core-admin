<div>
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl bg-violet-500/20 flex items-center justify-center">
                    <core:icon name="key" class="text-2xl text-violet-600 dark:text-violet-400" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Entitlements</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Manage what workspaces can access and how much they can use</p>
                </div>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-violet-500/20 text-violet-600 dark:text-violet-400">
                <core:icon name="crown" class="mr-1.5" />
                Hades Only
            </span>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-6 p-4 rounded-lg bg-green-500/20 text-green-700 dark:text-green-400">
        <div class="flex items-center">
            <core:icon name="check-circle" class="mr-2" />
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 rounded-lg bg-red-500/20 text-red-700 dark:text-red-400">
        <div class="flex items-center">
            <core:icon name="circle-xmark" class="mr-2" />
            {{ session('error') }}
        </div>
    </div>
    @endif

    {{-- Tabs --}}
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="flex gap-6" aria-label="Tabs">
            @foreach([
                'overview' => ['label' => 'Overview', 'icon' => 'gauge'],
                'packages' => ['label' => 'Packages', 'icon' => 'box'],
                'features' => ['label' => 'Features', 'icon' => 'puzzle-piece'],
            ] as $tabKey => $info)
            <button
                wire:click="setTab('{{ $tabKey }}')"
                class="flex items-center gap-2 py-3 px-1 border-b-2 text-sm font-medium transition {{ $tab === $tabKey ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
            >
                <core:icon name="{{ $info['icon'] }}" />
                {{ $info['label'] }}
            </button>
            @endforeach
        </nav>
    </div>

    {{-- Tab Content --}}
    <div class="min-h-[500px]">
        {{-- Overview Tab --}}
        @if($tab === 'overview')
        <div class="space-y-6">
            {{-- Explanation --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">How Entitlements Work</h3>
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    <p class="text-gray-600 dark:text-gray-400">
                        The entitlement system controls what workspaces can access and how much they can use. Think of it as a flexible permissions and quota system.
                    </p>

                    <div class="grid md:grid-cols-3 gap-6 mt-6 not-prose">
                        {{-- Features --}}
                        <div class="p-4 rounded-lg bg-blue-500/10 border border-blue-500/20">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                    <core:icon name="puzzle-piece" class="text-blue-600 dark:text-blue-400" />
                                </div>
                                <h4 class="font-semibold text-gray-800 dark:text-gray-100">Features</h4>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                The atomic building blocks. Each feature is something you can check: "Can they do X?" or "How many X can they have?"
                            </p>
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-gray-500/20 text-gray-600 dark:text-gray-400">boolean</span>
                                    <span class="text-gray-500">On/off access (e.g., core.srv.bio)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-600 dark:text-blue-400">limit</span>
                                    <span class="text-gray-500">Quota (e.g., bio.pages = 10)</span>
                                </div>
                            </div>
                        </div>

                        {{-- Packages --}}
                        <div class="p-4 rounded-lg bg-purple-500/10 border border-purple-500/20">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                    <core:icon name="box" class="text-purple-600 dark:text-purple-400" />
                                </div>
                                <h4 class="font-semibold text-gray-800 dark:text-gray-100">Packages</h4>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                Bundles of features sold as products. A "Pro" package might include 50 bio pages, social access, and analytics.
                            </p>
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-purple-500/20 text-purple-600 dark:text-purple-400">base</span>
                                    <span class="text-gray-500">One per workspace (plans)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-600 dark:text-blue-400">addon</span>
                                    <span class="text-gray-500">Stackable extras</span>
                                </div>
                            </div>
                        </div>

                        {{-- Boosts --}}
                        <div class="p-4 rounded-lg bg-amber-500/10 border border-amber-500/20">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center">
                                    <core:icon name="bolt" class="text-amber-600 dark:text-amber-400" />
                                </div>
                                <h4 class="font-semibold text-gray-800 dark:text-gray-100">Boosts</h4>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                One-off grants for specific features. Admin can give a workspace +100 pages or enable a feature temporarily.
                            </p>
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-green-500/20 text-green-600 dark:text-green-400">permanent</span>
                                    <span class="text-gray-500">Forever (or until revoked)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-600 dark:text-amber-400">expiring</span>
                                    <span class="text-gray-500">Time-limited</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 p-4 rounded-lg bg-gray-100 dark:bg-gray-700/30">
                        <h5 class="font-medium text-gray-800 dark:text-gray-200 mb-2">The Flow</h5>
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 flex-wrap">
                            <span class="px-2 py-1 rounded bg-blue-500/20 text-blue-700 dark:text-blue-300">Features</span>
                            <core:icon name="arrow-right" class="text-gray-400" />
                            <span class="text-gray-500">bundled into</span>
                            <core:icon name="arrow-right" class="text-gray-400" />
                            <span class="px-2 py-1 rounded bg-purple-500/20 text-purple-700 dark:text-purple-300">Packages</span>
                            <core:icon name="arrow-right" class="text-gray-400" />
                            <span class="text-gray-500">assigned to</span>
                            <core:icon name="arrow-right" class="text-gray-400" />
                            <span class="px-2 py-1 rounded bg-green-500/20 text-green-700 dark:text-green-300">Workspaces</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Boosts bypass packages to grant features directly to workspaces (for support, promotions, etc.)
                        </p>
                    </div>
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                            <core:icon name="box" class="text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $this->stats['packages']['total'] }}</div>
                            <div class="text-xs text-gray-500">Packages</div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-3 text-xs">
                        <span class="text-green-600">{{ $this->stats['packages']['active'] }} active</span>
                        <span class="text-gray-400">{{ $this->stats['packages']['public'] }} public</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                            <core:icon name="puzzle-piece" class="text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $this->stats['features']['total'] }}</div>
                            <div class="text-xs text-gray-500">Features</div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-3 text-xs">
                        <span class="text-gray-500">{{ $this->stats['features']['boolean'] }} boolean</span>
                        <span class="text-blue-500">{{ $this->stats['features']['limit'] }} limits</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                            <core:icon name="folder" class="text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $this->stats['assignments']['workspace_packages'] }}</div>
                            <div class="text-xs text-gray-500">Active Assignments</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-gray-500">
                        Workspaces with packages
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center">
                            <core:icon name="bolt" class="text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $this->stats['assignments']['active_boosts'] }}</div>
                            <div class="text-xs text-gray-500">Active Boosts</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-gray-500">
                        Direct feature grants
                    </div>
                </div>
            </div>

            {{-- Categories --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-5">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Feature Categories</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse($this->stats['categories'] as $category)
                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                        {{ $category }}
                    </span>
                    @empty
                    <span class="text-sm text-gray-400">No categories defined</span>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

        {{-- Packages Tab --}}
        @if($tab === 'packages')
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Packages</h2>
                    <p class="text-sm text-gray-500">Bundles of features assigned to workspaces</p>
                </div>
                <flux:button wire:click="openCreatePackage" icon="plus">
                    New Package
                </flux:button>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs overflow-hidden">
                <admin:manager-table
                    :columns="['Package', 'Code', 'Features', ['label' => 'Type', 'align' => 'center'], ['label' => 'Status', 'align' => 'center'], ['label' => 'Actions', 'align' => 'center']]"
                    :rows="$this->packageTableRows"
                    :pagination="$this->packages"
                    empty="No packages found. Create your first package to get started."
                    emptyIcon="box"
                />
            </div>
        </div>
        @endif

        {{-- Features Tab --}}
        @if($tab === 'features')
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Features</h2>
                    <p class="text-sm text-gray-500">Individual capabilities that can be checked and tracked</p>
                </div>
                <flux:button wire:click="openCreateFeature" icon="plus">
                    New Feature
                </flux:button>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs overflow-hidden">
                <admin:manager-table
                    :columns="['Feature', 'Code', 'Category', ['label' => 'Type', 'align' => 'center'], ['label' => 'Reset', 'align' => 'center'], ['label' => 'Status', 'align' => 'center'], ['label' => 'Actions', 'align' => 'center']]"
                    :rows="$this->featureTableRows"
                    :pagination="$this->features"
                    empty="No features found. Create your first feature to get started."
                    emptyIcon="puzzle-piece"
                />
            </div>
        </div>
        @endif
    </div>

    {{-- Package Modal --}}
    <flux:modal wire:model="showPackageModal" class="max-w-xl">
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center">
                    <core:icon name="box" class="text-purple-600 dark:text-purple-400" />
                </div>
                <flux:heading size="lg">{{ $editingPackageId ? 'Edit Package' : 'Create Package' }}</flux:heading>
            </div>

            <form wire:submit="savePackage" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="packageCode" label="Code" placeholder="pro" required />
                    <flux:input wire:model="packageName" label="Name" placeholder="Pro Plan" required />
                </div>

                <flux:textarea wire:model="packageDescription" label="Description" rows="2" />

                <div class="grid grid-cols-3 gap-4">
                    <flux:input wire:model="packageIcon" label="Icon" placeholder="box" />
                    <flux:input wire:model="packageColor" label="Colour" placeholder="blue" />
                    <flux:input wire:model="packageSortOrder" label="Sort Order" type="number" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:checkbox wire:model="packageIsBasePackage" label="Base Package" description="Only one per workspace" />
                    <flux:checkbox wire:model="packageIsStackable" label="Stackable" description="Can combine with others" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:checkbox wire:model="packageIsActive" label="Active" />
                    <flux:checkbox wire:model="packageIsPublic" label="Public" description="Show on pricing" />
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="closePackageModal" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingPackageId ? 'Update' : 'Create' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Feature Modal --}}
    <flux:modal wire:model="showFeatureModal" class="max-w-xl">
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                    <core:icon name="puzzle-piece" class="text-blue-600 dark:text-blue-400" />
                </div>
                <flux:heading size="lg">{{ $editingFeatureId ? 'Edit Feature' : 'Create Feature' }}</flux:heading>
            </div>

            <form wire:submit="saveFeature" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="featureCode" label="Code" placeholder="bio.pages" required />
                    <flux:input wire:model="featureName" label="Name" placeholder="Bio Pages" required />
                </div>

                <flux:textarea wire:model="featureDescription" label="Description" rows="2" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="featureCategory" label="Category" placeholder="biolink" />
                    <flux:input wire:model="featureSortOrder" label="Sort Order" type="number" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:select wire:model.live="featureType" label="Type">
                        <flux:select.option value="boolean">Boolean (on/off)</flux:select.option>
                        <flux:select.option value="limit">Limit (quota)</flux:select.option>
                        <flux:select.option value="unlimited">Unlimited</flux:select.option>
                    </flux:select>

                    <flux:select wire:model.live="featureResetType" label="Reset">
                        <flux:select.option value="none">Never</flux:select.option>
                        <flux:select.option value="monthly">Monthly</flux:select.option>
                        <flux:select.option value="rolling">Rolling Window</flux:select.option>
                    </flux:select>
                </div>

                @if($featureResetType === 'rolling')
                <flux:input wire:model="featureRollingDays" type="number" label="Rolling Window (days)" placeholder="30" />
                @endif

                @if($featureType === 'limit')
                <flux:select wire:model="featureParentId" label="Parent Pool (optional)">
                    <flux:select.option value="">None</flux:select.option>
                    @foreach($this->parentFeatures as $parent)
                    <flux:select.option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->code }})</flux:select.option>
                    @endforeach
                </flux:select>
                @endif

                <flux:checkbox wire:model="featureIsActive" label="Active" />

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="closeFeatureModal" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingFeatureId ? 'Update' : 'Create' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Features Assignment Modal --}}
    <flux:modal wire:model="showFeaturesModal" class="max-w-2xl">
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                    <core:icon name="puzzle-piece" class="text-green-600 dark:text-green-400" />
                </div>
                <flux:heading size="lg">Assign Features to Package</flux:heading>
            </div>

            <form wire:submit="saveFeatures" class="space-y-6">
                @foreach($this->allFeatures as $category => $categoryFeatures)
                <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2 capitalize">{{ $category ?: 'General' }}</h4>
                    <div class="space-y-2">
                        @foreach($categoryFeatures as $feature)
                        <div class="flex items-center gap-4 p-3 rounded-lg border border-gray-200 dark:border-gray-700 {{ isset($selectedFeatures[$feature->id]['enabled']) && $selectedFeatures[$feature->id]['enabled'] ? 'bg-green-500/5 border-green-500/30' : '' }}">
                            <flux:checkbox
                                wire:click="toggleFeature({{ $feature->id }})"
                                :checked="isset($selectedFeatures[$feature->id]['enabled']) && $selectedFeatures[$feature->id]['enabled']"
                            />
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-800 dark:text-gray-100">{{ $feature->name }}</div>
                                <code class="text-xs text-gray-500">{{ $feature->code }}</code>
                            </div>
                            @if($feature->type === 'limit')
                            <flux:input
                                type="number"
                                wire:model="selectedFeatures.{{ $feature->id }}.limit"
                                placeholder="Limit"
                                class="w-24"
                                :disabled="!isset($selectedFeatures[$feature->id]['enabled']) || !$selectedFeatures[$feature->id]['enabled']"
                            />
                            @elseif($feature->type === 'unlimited')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-500/20 text-purple-600">Unlimited</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-500/20 text-gray-600">Boolean</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="$set('showFeaturesModal', false)" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save Features</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
