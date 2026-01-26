<!-- Kanban Board -->
<core:kanban class="overflow-x-auto pb-4" style="min-height: 600px;">
    @foreach($this->kanbanColumns as $column)
        <core:kanban.column>
            <core:kanban.column.header
                :heading="$column['name']"
                :count="$column['items']->count()"
                :badge="$column['items']->count()"
                badge:color="{{ $column['color'] }}"
            >
                <x-slot:actions>
                    @if($column['status'] === 'draft')
                        <core:button size="sm" variant="ghost" icon="plus" />
                    @endif
                </x-slot:actions>
            </core:kanban.column.header>

            <core:kanban.column.cards class="max-h-[calc(100vh-300px)] overflow-y-auto">
                @forelse($column['items'] as $item)
                    <core:kanban.card as="button" wire:click="selectItem({{ $item->id }})">
                        <x-slot:header>
                            <x-content.type-badge :type="$item->type" />
                            <x-content.sync-badge :status="$item->sync_status" />
                        </x-slot:header>

                        <core:heading size="sm" class="line-clamp-2">{{ $item->title }}</core:heading>

                        @if($item->excerpt)
                            <core:text size="sm" class="line-clamp-2 mt-1">
                                {{ Str::limit($item->excerpt, 80) }}
                            </core:text>
                        @endif

                        <x-slot:footer>
                            @if($item->categories && $item->categories->isNotEmpty())
                                @foreach($item->categories->take(2) as $category)
                                    <core:badge size="sm" color="zinc">{{ $category->name }}</core:badge>
                                @endforeach
                                @if($item->categories->count() > 2)
                                    <core:badge size="sm" color="zinc">+{{ $item->categories->count() - 2 }}</core:badge>
                                @endif
                            @endif
                            <div class="flex-1"></div>
                            <core:text size="xs" class="text-zinc-400">
                                {{ $item->wp_created_at?->format('M j') ?? '-' }}
                            </core:text>
                        </x-slot:footer>
                    </core:kanban.card>
                @empty
                    <div class="text-center py-8 text-zinc-400 dark:text-zinc-500">
                        <core:icon name="inbox" class="size-8 mx-auto mb-2 opacity-50" />
                        <core:text size="sm">{{ __('hub::hub.content_manager.kanban.no_items') }}</core:text>
                    </div>
                @endforelse
            </core:kanban.column.cards>
        </core:kanban.column>
    @endforeach
</core:kanban>
