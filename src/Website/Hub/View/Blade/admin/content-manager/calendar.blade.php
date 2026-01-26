<!-- Calendar View -->
<core:card class="p-6">
    @php
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startDay = $startOfMonth->dayOfWeek;
        $daysInMonth = $now->daysInMonth;

        // Group events by date
        $eventsByDate = collect($this->calendarEvents)->groupBy('date');
    @endphp

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <core:heading>{{ $now->format('F Y') }}</core:heading>
            <core:subheading>{{ __('hub::hub.content_manager.calendar.content_schedule') }}</core:subheading>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                <core:text size="xs">{{ __('hub::hub.content_manager.calendar.legend.published') }}</core:text>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                <core:text size="xs">{{ __('hub::hub.content_manager.calendar.legend.draft') }}</core:text>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                <core:text size="xs">{{ __('hub::hub.content_manager.calendar.legend.scheduled') }}</core:text>
            </div>
        </div>
    </div>

    <!-- Body -->
        <!-- Weekday Headers -->
        <div class="grid grid-cols-7 gap-1 mb-2">
            @foreach([
                __('hub::hub.content_manager.calendar.days.sun'),
                __('hub::hub.content_manager.calendar.days.mon'),
                __('hub::hub.content_manager.calendar.days.tue'),
                __('hub::hub.content_manager.calendar.days.wed'),
                __('hub::hub.content_manager.calendar.days.thu'),
                __('hub::hub.content_manager.calendar.days.fri'),
                __('hub::hub.content_manager.calendar.days.sat')
            ] as $day)
                <div class="text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 py-2">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        <!-- Calendar Grid -->
        <div class="grid grid-cols-7 gap-1">
            {{-- Empty cells for days before start of month --}}
            @for($i = 0; $i < $startDay; $i++)
                <div class="aspect-square p-1 bg-zinc-50 dark:bg-zinc-800/30 rounded-lg"></div>
            @endfor

            {{-- Days of the month --}}
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateStr = $now->copy()->setDay($day)->format('Y-m-d');
                    $dayEvents = $eventsByDate->get($dateStr, collect());
                    $isToday = $now->copy()->setDay($day)->isToday();
                @endphp
                <div class="aspect-square p-1 {{ $isToday ? 'bg-violet-50 dark:bg-violet-500/10 ring-2 ring-violet-500' : 'bg-zinc-50 dark:bg-zinc-800/30' }} rounded-lg overflow-hidden">
                    <div class="text-xs font-medium {{ $isToday ? 'text-violet-600 dark:text-violet-400' : 'text-zinc-600 dark:text-zinc-400' }} mb-1">
                        {{ $day }}
                    </div>
                    <div class="space-y-0.5 max-h-16 overflow-hidden">
                        @foreach($dayEvents->take(3) as $event)
                            <button wire:click="selectItem({{ $event['id'] }})"
                                 class="w-full text-left text-xs px-1 py-0.5 rounded truncate cursor-pointer
                                        {{ $event['status'] === 'publish' ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400' :
                                           ($event['status'] === 'future' ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400' :
                                           'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400') }}">
                                {{ Str::limit($event['title'], 15) }}
                            </button>
                        @endforeach
                        @if($dayEvents->count() > 3)
                            <div class="text-xs text-zinc-400 dark:text-zinc-500 px-1">
                                {{ __('hub::hub.content_manager.calendar.more', ['count' => $dayEvents->count() - 3]) }}
                            </div>
                        @endif
                    </div>
                </div>
            @endfor

            {{-- Empty cells for days after end of month --}}
            @php
                $remainingCells = 7 - (($startDay + $daysInMonth) % 7);
                if ($remainingCells == 7) $remainingCells = 0;
            @endphp
            @for($i = 0; $i < $remainingCells; $i++)
                <div class="aspect-square p-1 bg-zinc-50 dark:bg-zinc-800/30 rounded-lg"></div>
            @endfor
        </div>
</core:card>
