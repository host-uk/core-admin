{{--
Global search component with Command+K keyboard shortcut.

Include in your layout:
<livewire:hub.admin.global-search />

Features:
- Command+K / Ctrl+K to open
- Arrow key navigation (up/down)
- Enter to select
- Escape to close
- Recent searches
- Grouped results by provider type
--}}

<div
    x-data="{
        init() {
            // Listen for Command+K / Ctrl+K keyboard shortcut
            document.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    $wire.openSearch();
                }
            });
        }
    }"
    x-on:navigate-to-url.window="Livewire.navigate($event.detail.url)"
>
    {{-- Search modal --}}
    <core:modal wire:model="open" class="max-w-xl" variant="bare">
        <div
            class="overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-zinc-900/5 dark:bg-zinc-800 dark:ring-zinc-700"
            x-on:keydown.arrow-up.prevent="$wire.navigateUp()"
            x-on:keydown.arrow-down.prevent="$wire.navigateDown()"
            x-on:keydown.enter.prevent="$wire.selectCurrent()"
            x-on:keydown.escape.prevent="$wire.closeSearch()"
        >
            {{-- Search input --}}
            <div class="relative">
                <core:icon name="magnifying-glass" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-400" />
                <input
                    wire:model.live.debounce.300ms="query"
                    type="text"
                    placeholder="{{ __('hub::hub.search.placeholder') }}"
                    class="w-full border-0 bg-transparent py-4 pl-12 pr-4 text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-white"
                    autofocus
                />
                @if($query)
                    <button
                        wire:click="$set('query', '')"
                        type="button"
                        class="absolute right-4 top-1/2 -translate-y-1/2 rounded p-1 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                    >
                        <core:icon name="x-mark" class="h-4 w-4" />
                    </button>
                @endif
            </div>

            {{-- Results --}}
            @if(strlen($query) >= 2)
                <div class="max-h-96 overflow-y-auto border-t border-zinc-200 dark:border-zinc-700">
                    @php $currentIndex = 0; @endphp

                    @forelse($this->results as $type => $group)
                        @if(count($group['results']) > 0)
                            {{-- Category header --}}
                            <div class="sticky top-0 z-10 flex items-center gap-2 bg-zinc-50 px-4 py-2 dark:bg-zinc-800/80 backdrop-blur-sm">
                                <core:icon :name="$group['icon']" class="h-3.5 w-3.5 text-zinc-400" />
                                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ $group['label'] }}
                                </span>
                            </div>

                            {{-- Results list --}}
                            @foreach($group['results'] as $item)
                                <button
                                    wire:click="navigateTo({{ json_encode($item) }})"
                                    type="button"
                                    class="flex w-full items-center gap-3 px-4 py-3 text-left transition {{ $selectedIndex === $currentIndex ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-zinc-50 dark:hover:bg-zinc-700/50' }}"
                                >
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $selectedIndex === $currentIndex ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' }}">
                                        <core:icon :name="$item['icon']" class="h-5 w-5" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate font-medium {{ $selectedIndex === $currentIndex ? 'text-blue-900 dark:text-blue-100' : 'text-zinc-900 dark:text-white' }}">
                                            {{ $item['title'] }}
                                        </div>
                                        @if($item['subtitle'])
                                            <div class="truncate text-sm {{ $selectedIndex === $currentIndex ? 'text-blue-600 dark:text-blue-300' : 'text-zinc-500 dark:text-zinc-400' }}">
                                                {{ $item['subtitle'] }}
                                            </div>
                                        @endif
                                    </div>
                                    @if($selectedIndex === $currentIndex)
                                        <div class="flex items-center gap-1">
                                            <kbd class="rounded bg-blue-100 px-1.5 py-0.5 text-xs font-mono text-blue-600 dark:bg-blue-900/40 dark:text-blue-400">
                                                Enter
                                            </kbd>
                                            <core:icon name="arrow-right" class="h-4 w-4 text-blue-500" />
                                        </div>
                                    @endif
                                </button>
                                @php $currentIndex++; @endphp
                            @endforeach
                        @endif
                    @empty
                        {{-- No results --}}
                        <div class="px-4 py-12 text-center">
                            <core:icon name="magnifying-glass" class="mx-auto h-10 w-10 text-zinc-300 dark:text-zinc-600" />
                            <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('hub::hub.search.no_results', ['query' => $query]) }}
                            </p>
                        </div>
                    @endforelse

                    @if(!$this->hasResults && strlen($query) >= 2)
                        <div class="px-4 py-12 text-center">
                            <core:icon name="magnifying-glass" class="mx-auto h-10 w-10 text-zinc-300 dark:text-zinc-600" />
                            <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('hub::hub.search.no_results', ['query' => $query]) }}
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Footer with keyboard hints --}}
                <div class="flex items-center justify-between border-t border-zinc-200 bg-zinc-50 px-4 py-2 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="flex items-center gap-4 text-xs text-zinc-400">
                        <span class="flex items-center gap-1">
                            <kbd class="rounded bg-zinc-200 px-1.5 py-0.5 font-mono dark:bg-zinc-700">↑</kbd>
                            <kbd class="rounded bg-zinc-200 px-1.5 py-0.5 font-mono dark:bg-zinc-700">↓</kbd>
                            {{ __('hub::hub.search.navigate') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <kbd class="rounded bg-zinc-200 px-1.5 py-0.5 font-mono dark:bg-zinc-700">↵</kbd>
                            {{ __('hub::hub.search.select') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <kbd class="rounded bg-zinc-200 px-1.5 py-0.5 font-mono dark:bg-zinc-700">esc</kbd>
                            {{ __('hub::hub.search.close') }}
                        </span>
                    </div>
                </div>

            @elseif($this->showRecentSearches)
                {{-- Recent searches --}}
                <div class="border-t border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between px-4 py-2 bg-zinc-50 dark:bg-zinc-800/80">
                        <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('hub::hub.search.recent') }}
                        </span>
                        <button
                            wire:click="clearRecentSearches"
                            type="button"
                            class="text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                        >
                            {{ __('hub::hub.search.clear_recent') }}
                        </button>
                    </div>
                    <div class="max-h-72 overflow-y-auto">
                        @foreach($recentSearches as $index => $recent)
                            <div class="group flex items-center">
                                <button
                                    wire:click="navigateToRecent({{ $index }})"
                                    type="button"
                                    class="flex flex-1 items-center gap-3 px-4 py-3 text-left hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition"
                                >
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100 text-zinc-400 dark:bg-zinc-700 dark:text-zinc-500">
                                        <core:icon :name="$recent['icon']" class="h-4 w-4" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate text-sm font-medium text-zinc-700 dark:text-zinc-200">
                                            {{ $recent['title'] }}
                                        </div>
                                        @if($recent['subtitle'])
                                            <div class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $recent['subtitle'] }}
                                            </div>
                                        @endif
                                    </div>
                                    <core:icon name="clock-rotate-left" class="h-4 w-4 text-zinc-300 dark:text-zinc-600" />
                                </button>
                                <button
                                    wire:click="removeRecentSearch({{ $index }})"
                                    type="button"
                                    class="p-2 mr-2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 opacity-0 group-hover:opacity-100 transition-opacity"
                                    title="{{ __('hub::hub.search.remove') }}"
                                >
                                    <core:icon name="x-mark" class="h-4 w-4" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

            @else
                {{-- Initial state --}}
                <div class="border-t border-zinc-200 px-4 py-12 text-center dark:border-zinc-700">
                    <core:icon name="magnifying-glass" class="mx-auto h-10 w-10 text-zinc-300 dark:text-zinc-600" />
                    <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('hub::hub.search.start_typing') }}
                    </p>
                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                        {{ __('hub::hub.search.tips') }}
                    </p>
                </div>
            @endif
        </div>
    </core:modal>
</div>
