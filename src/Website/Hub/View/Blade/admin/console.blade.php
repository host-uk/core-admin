<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">{{ __('hub::hub.console.title') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('hub::hub.console.subtitle') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Server list -->
        <div class="col-span-full lg:col-span-4 xl:col-span-3">
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100">{{ __('hub::hub.console.labels.select_server') }}</h2>
                </header>
                <div class="p-3">
                    <ul class="space-y-2">
                        @foreach($servers as $server)
                        <li>
                            <button
                                wire:click="selectServer({{ $server['id'] }})"
                                class="w-full flex items-center p-3 rounded-lg transition {{ $selectedServer === $server['id'] ? 'bg-violet-500/10 border border-violet-500/50' : 'bg-gray-50 dark:bg-gray-700/30 hover:bg-gray-100 dark:hover:bg-gray-700/50 border border-transparent' }}"
                            >
                                <div class="w-8 h-8 rounded-lg {{ $selectedServer === $server['id'] ? 'bg-violet-500/20' : 'bg-gray-200 dark:bg-gray-600' }} flex items-center justify-center mr-3">
                                    @switch($server['type'])
                                        @case('WordPress')
                                            <i class="fa-brands fa-wordpress {{ $selectedServer === $server['id'] ? 'text-violet-500' : 'text-gray-500 dark:text-gray-400' }} text-sm"></i>
                                            @break
                                        @case('Laravel')
                                            <i class="fa-brands fa-laravel {{ $selectedServer === $server['id'] ? 'text-violet-500' : 'text-gray-500 dark:text-gray-400' }} text-sm"></i>
                                            @break
                                        @case('Node.js')
                                            <i class="fa-brands fa-node-js {{ $selectedServer === $server['id'] ? 'text-violet-500' : 'text-gray-500 dark:text-gray-400' }} text-sm"></i>
                                            @break
                                        @default
                                            <core:icon name="server" class="{{ $selectedServer === $server['id'] ? 'text-violet-500' : 'text-gray-500 dark:text-gray-400' }} text-sm" />
                                    @endswitch
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-medium {{ $selectedServer === $server['id'] ? 'text-violet-600 dark:text-violet-400' : 'text-gray-800 dark:text-gray-100' }}">{{ $server['name'] }}</div>
                                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                                        <div class="w-1.5 h-1.5 rounded-full {{ $server['status'] === 'online' ? 'bg-green-500' : 'bg-red-500' }} mr-1"></div>
                                        {{ ucfirst($server['status']) }}
                                    </div>
                                </div>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Coolify Integration Notice -->
            <div class="bg-violet-500/10 border border-violet-500/20 rounded-xl p-4 mt-6">
                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-lg bg-violet-500/20 flex items-center justify-center mr-3 shrink-0">
                        <core:icon name="plug" class="text-violet-500" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-1">{{ __('hub::hub.console.coolify.title') }}</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('hub::hub.console.coolify.description') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terminal -->
        <div class="col-span-full lg:col-span-8 xl:col-span-9">
            <div class="bg-gray-900 rounded-xl overflow-hidden shadow-xl h-[600px] flex flex-col">
                <!-- Terminal header -->
                <div class="flex items-center justify-between px-4 py-3 bg-gray-800 border-b border-gray-700">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    </div>
                    @if($selectedServer)
                        @php $selectedServerData = collect($servers)->firstWhere('id', $selectedServer); @endphp
                        <span class="text-sm text-gray-400">{{ $selectedServerData['name'] ?? __('hub::hub.console.labels.terminal') }}</span>
                    @else
                        <span class="text-sm text-gray-400">{{ __('hub::hub.console.labels.terminal') }}</span>
                    @endif
                    <div class="flex items-center space-x-2">
                        <core:button variant="ghost" size="sm" icon="arrows-pointing-out" class="text-gray-400 hover:text-white" />
                        <core:button variant="ghost" size="sm" icon="cog-6-tooth" class="text-gray-400 hover:text-white" />
                    </div>
                </div>

                <!-- Terminal body -->
                <div class="flex-1 p-4 font-mono text-sm overflow-auto">
                    @if($selectedServer)
                        <div class="text-green-400">
                            <div class="mb-2">{{ __('hub::hub.console.labels.connecting', ['name' => $selectedServerData['name'] ?? 'server']) }}</div>
                            <div class="mb-2 text-gray-500">{{ __('hub::hub.console.labels.establishing_connection') }}</div>
                            <div class="mb-4 text-green-400">{{ __('hub::hub.console.labels.connected') }}</div>
                            <div class="text-gray-300">
                                <span class="text-violet-400">root@{{ strtolower(str_replace(' ', '-', $selectedServerData['name'] ?? 'server')) }}</span>:<span class="text-blue-400">~</span>$
                                <span class="animate-pulse">_</span>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-full text-gray-500">
                            <core:icon name="terminal" class="text-4xl mb-4 opacity-50" />
                            <p class="text-center">{{ __('hub::hub.console.labels.select_server_prompt') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Terminal input -->
                @if($selectedServer)
                <div class="border-t border-gray-700 p-2">
                    <div class="flex items-center bg-gray-800 rounded px-3 py-2">
                        <span class="text-gray-400 mr-2">$</span>
                        <input
                            type="text"
                            class="flex-1 bg-transparent text-gray-100 focus:outline-none font-mono text-sm"
                            placeholder="{{ __('hub::hub.console.labels.enter_command') }}"
                            disabled
                        >
                        <core:button variant="ghost" size="sm" icon="paper-airplane" class="text-gray-400 hover:text-white ml-2" />
                    </div>
                    <p class="text-xs text-gray-500 mt-2 px-1">
                        <core:icon name="info-circle" class="mr-1" />
                        {{ __('hub::hub.console.labels.terminal_disabled') }}
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
