@php
    $user = auth()->user();
    $showDevBar = $user && method_exists($user, 'isHades') && $user->isHades();

    // Performance metrics
    $queryCount = count(DB::getQueryLog());
    $startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
    $loadTime = number_format((microtime(true) - $startTime) * 1000, 2);
    $memoryUsage = number_format(memory_get_peak_usage(true) / 1024 / 1024, 1);

    // Check available dev tools
    $hasHorizon = class_exists(\Laravel\Horizon\Horizon::class);
    $hasPulse = class_exists(\Laravel\Pulse\Pulse::class);
    $hasTelescope = class_exists(\Laravel\Telescope\Telescope::class) && config('telescope.enabled', false);
@endphp

@if($showDevBar)
<div
    x-data="{
        expanded: false,
        activePanel: null,
        logs: [],
        routes: [],
        routeFilter: '',
        session: {},
        loadingLogs: false,
        loadingRoutes: false,

        togglePanel(panel) {
            if (this.activePanel === panel) {
                this.activePanel = null;
            } else {
                this.activePanel = panel;
                if (panel === 'logs' && this.logs.length === 0) this.loadLogs();
                if (panel === 'routes' && this.routes.length === 0) this.loadRoutes();
                if (panel === 'session') this.loadSession();
            }
        },

        async loadLogs() {
            this.loadingLogs = true;
            try {
                const res = await fetch('/hub/api/dev/logs');
                this.logs = await res.json();
            } catch (e) {
                this.logs = [{ level: 'error', message: 'Failed to load logs', time: new Date().toISOString() }];
            }
            this.loadingLogs = false;
        },

        async loadRoutes() {
            this.loadingRoutes = true;
            try {
                const res = await fetch('/hub/api/dev/routes');
                this.routes = await res.json();
            } catch (e) {
                this.routes = [];
            }
            this.loadingRoutes = false;
        },

        async loadSession() {
            try {
                const res = await fetch('/hub/api/dev/session');
                this.session = await res.json();
            } catch (e) {
                this.session = { error: 'Failed to load session' };
            }
        },

        async clearCache(type) {
            try {
                const res = await fetch('/hub/api/dev/clear/' + type, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }});
                const data = await res.json();
                alert(data.message || 'Done!');
            } catch (e) {
                alert('Failed: ' + e.message);
            }
        }
    }"
    class="fixed bottom-0 left-0 right-0 z-50"
>
    <!-- Expandable Panel Area -->
    <div
        x-show="activePanel"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="border-t border-violet-500/50 shadow-2xl"
        style="background-color: #0a0a0f; max-height: 53vh; overflow-y: auto;"
    >
        <!-- Logs Panel -->
        <div x-show="activePanel === 'logs'" class="p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-violet-400 font-semibold text-sm">Recent Logs</h3>
                <button @click="loadLogs()" class="text-xs text-gray-400 hover:text-white">
                    <i class="fa-solid fa-refresh" :class="{ 'animate-spin': loadingLogs }"></i> Refresh
                </button>
            </div>
            <div class="space-y-1 font-mono text-xs">
                <template x-if="loadingLogs">
                    <div class="text-gray-500">Loading...</div>
                </template>
                <template x-if="!loadingLogs && logs.length === 0">
                    <div class="text-gray-500">No recent logs</div>
                </template>
                <template x-for="log in logs" :key="log.time">
                    <div class="flex items-start gap-2 py-1 border-b border-gray-800">
                        <span
                            class="px-1.5 py-0.5 rounded text-[10px] uppercase font-bold"
                            :class="{
                                'bg-red-500/20 text-red-400': log.level === 'error',
                                'bg-yellow-500/20 text-yellow-400': log.level === 'warning',
                                'bg-blue-500/20 text-blue-400': log.level === 'info',
                                'bg-gray-500/20 text-gray-400': !['error', 'warning', 'info'].includes(log.level)
                            }"
                            x-text="log.level"
                        ></span>
                        <span class="text-gray-500" x-text="log.time"></span>
                        <span class="text-gray-300 flex-1 truncate" x-text="log.message"></span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Routes Panel -->
        <div x-show="activePanel === 'routes'" class="p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-violet-400 font-semibold text-sm">Routes</h3>
                <input
                    type="text"
                    placeholder="Filter routes..."
                    class="bg-gray-800 border border-gray-700 rounded px-2 py-1 text-xs text-white w-48"
                    x-model="routeFilter"
                >
            </div>
            <div class="space-y-1 font-mono text-xs max-h-48 overflow-y-auto">
                <template x-if="loadingRoutes">
                    <div class="text-gray-500">Loading...</div>
                </template>
                <template x-for="route in routes.filter(r => !routeFilter || r.uri.includes(routeFilter) || (r.name && r.name.includes(routeFilter)))" :key="route.uri + route.method">
                    <div class="flex items-center gap-2 py-1 border-b border-gray-800">
                        <span
                            class="px-1.5 py-0.5 rounded text-[10px] uppercase font-bold w-14 text-center"
                            :class="{
                                'bg-green-500/20 text-green-400': route.method === 'GET',
                                'bg-blue-500/20 text-blue-400': route.method === 'POST',
                                'bg-yellow-500/20 text-yellow-400': route.method === 'PUT' || route.method === 'PATCH',
                                'bg-red-500/20 text-red-400': route.method === 'DELETE',
                            }"
                            x-text="route.method"
                        ></span>
                        <span class="text-gray-300" x-text="route.uri"></span>
                        <span class="text-gray-500 text-[10px]" x-text="route.name || ''"></span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Session Panel -->
        <div x-show="activePanel === 'session'" class="p-4">
            <h3 class="text-violet-400 font-semibold text-sm mb-3">Session & Request</h3>
            <div class="grid grid-cols-2 gap-4 text-xs font-mono">
                <div>
                    <div class="text-gray-500 mb-1">Session ID</div>
                    <div class="text-gray-300 truncate" x-text="session.id || '-'"></div>
                </div>
                <div>
                    <div class="text-gray-500 mb-1">User Agent</div>
                    <div class="text-gray-300 truncate" x-text="session.user_agent || '-'"></div>
                </div>
                <div>
                    <div class="text-gray-500 mb-1">IP Address</div>
                    <div class="text-gray-300" x-text="session.ip || '-'"></div>
                </div>
                <div>
                    <div class="text-gray-500 mb-1">PHP Version</div>
                    <div class="text-gray-300">{{ PHP_VERSION }}</div>
                </div>
                <div>
                    <div class="text-gray-500 mb-1">Laravel Version</div>
                    <div class="text-gray-300">{{ app()->version() }}</div>
                </div>
                <div>
                    <div class="text-gray-500 mb-1">Environment</div>
                    <div class="text-gray-300">{{ app()->environment() }}</div>
                </div>
            </div>
        </div>

        <!-- Cache Panel -->
        <div x-show="activePanel === 'cache'" class="p-4">
            <h3 class="text-violet-400 font-semibold text-sm mb-3">Cache Management</h3>
            <div class="flex flex-wrap gap-2">
                <button @click="clearCache('cache')" class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded text-xs transition-colors">
                    <i class="fa-solid fa-trash mr-1"></i> Clear Cache
                </button>
                <button @click="clearCache('config')" class="px-3 py-1.5 bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-400 rounded text-xs transition-colors">
                    <i class="fa-solid fa-gear mr-1"></i> Clear Config
                </button>
                <button @click="clearCache('view')" class="px-3 py-1.5 bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 rounded text-xs transition-colors">
                    <i class="fa-solid fa-eye mr-1"></i> Clear Views
                </button>
                <button @click="clearCache('route')" class="px-3 py-1.5 bg-green-500/20 hover:bg-green-500/30 text-green-400 rounded text-xs transition-colors">
                    <i class="fa-solid fa-route mr-1"></i> Clear Routes
                </button>
                <button @click="clearCache('all')" class="px-3 py-1.5 bg-violet-500/20 hover:bg-violet-500/30 text-violet-400 rounded text-xs transition-colors">
                    <i class="fa-solid fa-bomb mr-1"></i> Clear All
                </button>
            </div>
            <p class="text-gray-500 text-xs mt-3">
                <i class="fa-solid fa-info-circle mr-1"></i>
                These actions run artisan cache commands on the server.
            </p>
        </div>

        <!-- Appearance Panel -->
        <div x-show="activePanel === 'appearance'" class="p-4" x-data="{
            iconStyle: localStorage.getItem('icon-style') || 'fa-notdog fa-solid',
            iconSize: localStorage.getItem('icon-size') || 'fa-lg',
            setStyle(style) {
                this.iconStyle = style;
                localStorage.setItem('icon-style', style);
                document.cookie = 'icon-style=' + style + '; path=/; SameSite=Lax';
                location.reload();
            },
            setSize(size) {
                this.iconSize = size;
                localStorage.setItem('icon-size', size);
                document.cookie = 'icon-size=' + size + '; path=/; SameSite=Lax';
                location.reload();
            }
        }">
            <!-- Classic Families -->
            <h3 class="text-violet-400 font-semibold text-sm mb-2">Classic</h3>
            <div class="grid grid-cols-4 md:grid-cols-5 lg:grid-cols-10 gap-2 mb-4">
                <button @click="setStyle('fa-solid')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-solid' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-solid fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Solid</span>
                </button>
                <button @click="setStyle('fa-regular')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-regular' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-regular fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Regular</span>
                </button>
                <button @click="setStyle('fa-light')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-light' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-light fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Light</span>
                </button>
                <button @click="setStyle('fa-thin')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-thin' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-thin fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Thin</span>
                </button>
                <button @click="setStyle('fa-duotone')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-duotone' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-duotone fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Duotone</span>
                </button>
            </div>

            <!-- Sharp Families -->
            <h3 class="text-violet-400 font-semibold text-sm mb-2">Sharp</h3>
            <div class="grid grid-cols-4 md:grid-cols-5 lg:grid-cols-10 gap-2 mb-4">
                <button @click="setStyle('fa-sharp fa-solid')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-sharp fa-solid' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-sharp fa-solid fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Solid</span>
                </button>
                <button @click="setStyle('fa-sharp fa-regular')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-sharp fa-regular' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-sharp fa-regular fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Regular</span>
                </button>
                <button @click="setStyle('fa-sharp fa-light')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-sharp fa-light' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-sharp fa-light fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Light</span>
                </button>
                <button @click="setStyle('fa-sharp fa-thin')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-sharp fa-thin' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-sharp fa-thin fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Thin</span>
                </button>
                <button @click="setStyle('fa-sharp-duotone-solid')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-sharp-duotone-solid' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-sharp-duotone-solid fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Duo Solid</span>
                </button>
            </div>

            <!-- Specialty Families -->
            <h3 class="text-violet-400 font-semibold text-sm mb-2">Specialty</h3>
            <div class="grid grid-cols-4 md:grid-cols-5 lg:grid-cols-10 gap-2 mb-4">
                <button @click="setStyle('fa-jelly fa-regular')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-jelly fa-regular' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-jelly fa-regular fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Jelly</span>
                </button>
                <button @click="setStyle('fa-jelly-fill fa-regular')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-jelly-fill fa-regular' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-jelly-fill fa-regular fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Jelly Fill</span>
                </button>
                <button @click="setStyle('fa-jelly-duo fa-regular')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-jelly-duo fa-regular' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-jelly-duo fa-regular fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Jelly Duo</span>
                </button>
                <button @click="setStyle('fa-notdog fa-solid')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-notdog fa-solid' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-notdog fa-solid fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Notdog</span>
                </button>
                <button @click="setStyle('fa-notdog-duo fa-solid')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-notdog-duo fa-solid' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-notdog-duo fa-solid fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Notdog Duo</span>
                </button>
                <button @click="setStyle('fa-slab fa-regular')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-slab fa-regular' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-slab fa-regular fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Slab</span>
                </button>
                <button @click="setStyle('fa-slab-press fa-regular')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-slab-press fa-regular' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-slab-press fa-regular fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Slab Press</span>
                </button>
                <button @click="setStyle('fa-utility fa-semibold')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-utility fa-semibold' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-utility fa-semibold fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Utility</span>
                </button>
                <button @click="setStyle('fa-utility-fill fa-semibold')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-utility-fill fa-semibold' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-utility-fill fa-semibold fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Utility Fill</span>
                </button>
                <button @click="setStyle('fa-utility-duo fa-semibold')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-utility-duo fa-semibold' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-utility-duo fa-semibold fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Utility Duo</span>
                </button>
                <button @click="setStyle('fa-whiteboard fa-semibold')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-whiteboard fa-semibold' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-whiteboard fa-semibold fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Whiteboard</span>
                </button>
                <button @click="setStyle('fa-chisel fa-regular')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-chisel fa-regular' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-chisel fa-regular fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Chisel</span>
                </button>
                <button @click="setStyle('fa-etch fa-solid')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-etch fa-solid' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-etch fa-solid fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Etch</span>
                </button>
                <button @click="setStyle('fa-thumbprint fa-light')" class="flex flex-col items-center gap-1 p-2 rounded-lg border transition-colors" :class="iconStyle === 'fa-thumbprint fa-light' ? 'border-violet-500 bg-violet-500/10' : 'border-gray-700 hover:border-gray-600'">
                    <i class="fa-thumbprint fa-light fa-house text-xl text-gray-300"></i>
                    <span class="text-[10px] text-gray-400">Thumbprint</span>
                </button>
            </div>

            <!-- Icon Size -->
            <h3 class="text-violet-400 font-semibold text-sm mb-2">Size</h3>
            <div class="flex flex-wrap gap-2 mb-3">
                <button @click="setSize('')" class="px-3 py-1.5 rounded-lg border text-xs transition-colors" :class="iconSize === '' ? 'border-violet-500 bg-violet-500/10 text-violet-300' : 'border-gray-700 text-gray-400 hover:border-gray-600'">
                    Default
                </button>
                <button @click="setSize('fa-2xs')" class="px-3 py-1.5 rounded-lg border text-xs transition-colors" :class="iconSize === 'fa-2xs' ? 'border-violet-500 bg-violet-500/10 text-violet-300' : 'border-gray-700 text-gray-400 hover:border-gray-600'">
                    <i class="fa-solid fa-house fa-2xs mr-1"></i> 2XS
                </button>
                <button @click="setSize('fa-xs')" class="px-3 py-1.5 rounded-lg border text-xs transition-colors" :class="iconSize === 'fa-xs' ? 'border-violet-500 bg-violet-500/10 text-violet-300' : 'border-gray-700 text-gray-400 hover:border-gray-600'">
                    <i class="fa-solid fa-house fa-xs mr-1"></i> XS
                </button>
                <button @click="setSize('fa-sm')" class="px-3 py-1.5 rounded-lg border text-xs transition-colors" :class="iconSize === 'fa-sm' ? 'border-violet-500 bg-violet-500/10 text-violet-300' : 'border-gray-700 text-gray-400 hover:border-gray-600'">
                    <i class="fa-solid fa-house fa-sm mr-1"></i> SM
                </button>
                <button @click="setSize('fa-lg')" class="px-3 py-1.5 rounded-lg border text-xs transition-colors" :class="iconSize === 'fa-lg' ? 'border-violet-500 bg-violet-500/10 text-violet-300' : 'border-gray-700 text-gray-400 hover:border-gray-600'">
                    <i class="fa-solid fa-house fa-lg mr-1"></i> LG
                </button>
                <button @click="setSize('fa-xl')" class="px-3 py-1.5 rounded-lg border text-xs transition-colors" :class="iconSize === 'fa-xl' ? 'border-violet-500 bg-violet-500/10 text-violet-300' : 'border-gray-700 text-gray-400 hover:border-gray-600'">
                    <i class="fa-solid fa-house fa-xl mr-1"></i> XL
                </button>
                <button @click="setSize('fa-2xl')" class="px-3 py-1.5 rounded-lg border text-xs transition-colors" :class="iconSize === 'fa-2xl' ? 'border-violet-500 bg-violet-500/10 text-violet-300' : 'border-gray-700 text-gray-400 hover:border-gray-600'">
                    <i class="fa-solid fa-house fa-2xl mr-1"></i> 2XL
                </button>
            </div>

            <p class="text-gray-500 text-xs">
                <i class="fa-solid fa-info-circle mr-1"></i>
                Current: <code class="text-violet-400" x-text="iconStyle"></code>
                <span x-show="iconSize"> + <code class="text-violet-400" x-text="iconSize"></code></span>
            </p>
        </div>
    </div>

    <!-- Main Bar -->
    <div class="border-t border-violet-500/50 text-white text-xs font-mono shadow-lg" style="background-color: #0a0a0f;">
        <div class="flex items-center justify-between px-4 py-2">
            <!-- Left: Environment & User Info -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-red-500/20 text-red-400 rounded text-[10px] font-semibold uppercase">
                        {{ app()->environment() }}
                    </span>
                    <span class="text-gray-600">|</span>
                    <span class="text-violet-300">
                        <i class="fa-solid fa-bolt mr-1"></i>Hades
                    </span>
                </div>
                <div class="hidden sm:flex items-center gap-2 text-gray-400">
                    <i class="fa-solid fa-user text-xs"></i>
                    <span>{{ $user->name }}</span>
                </div>
            </div>

            <!-- Panel Toggle Buttons (positioned left of center) -->
            <div class="flex items-center gap-2 ml-8">
                <button
                    @click="togglePanel('logs')"
                    class="flex items-center justify-center w-9 h-9 rounded-lg transition-colors"
                    :class="activePanel === 'logs' ? 'bg-violet-500/30 text-violet-300' : 'hover:bg-gray-800 text-gray-400 hover:text-white'"
                    title="View Logs"
                >
                    <i class="fa-solid fa-scroll text-lg"></i>
                </button>

                <button
                    @click="togglePanel('routes')"
                    class="flex items-center justify-center w-9 h-9 rounded-lg transition-colors"
                    :class="activePanel === 'routes' ? 'bg-violet-500/30 text-violet-300' : 'hover:bg-gray-800 text-gray-400 hover:text-white'"
                    title="View Routes"
                >
                    <i class="fa-solid fa-route text-lg"></i>
                </button>

                <button
                    @click="togglePanel('session')"
                    class="flex items-center justify-center w-9 h-9 rounded-lg transition-colors"
                    :class="activePanel === 'session' ? 'bg-violet-500/30 text-violet-300' : 'hover:bg-gray-800 text-gray-400 hover:text-white'"
                    title="Session Info"
                >
                    <i class="fa-solid fa-fingerprint text-lg"></i>
                </button>

                <button
                    @click="togglePanel('cache')"
                    class="flex items-center justify-center w-9 h-9 rounded-lg transition-colors"
                    :class="activePanel === 'cache' ? 'bg-violet-500/30 text-violet-300' : 'hover:bg-gray-800 text-gray-400 hover:text-white'"
                    title="Cache Management"
                >
                    <i class="fa-solid fa-database text-lg"></i>
                </button>

                <button
                    @click="togglePanel('appearance')"
                    class="flex items-center justify-center w-9 h-9 rounded-lg transition-colors"
                    :class="activePanel === 'appearance' ? 'bg-violet-500/30 text-violet-300' : 'hover:bg-gray-800 text-gray-400 hover:text-white'"
                    title="Appearance & Icons"
                >
                    <i class="fa-solid fa-palette text-lg"></i>
                </button>

                <span class="text-gray-700 mx-2">|</span>

                <!-- External Dev Tools -->
                @if($hasHorizon)
                <a href="/horizon" target="_blank" class="flex items-center justify-center w-9 h-9 rounded-lg hover:bg-green-500/20 text-gray-400 hover:text-green-400 transition-colors" title="Laravel Horizon">
                    <i class="fa-solid fa-chart-line text-lg"></i>
                </a>
                @endif

                @if($hasPulse)
                <a href="/pulse" target="_blank" class="flex items-center justify-center w-9 h-9 rounded-lg hover:bg-pink-500/20 text-gray-400 hover:text-pink-400 transition-colors" title="Laravel Pulse">
                    <i class="fa-solid fa-heart-pulse text-lg"></i>
                </a>
                @endif

                @if($hasTelescope)
                <a href="/telescope" target="_blank" class="flex items-center justify-center w-9 h-9 rounded-lg hover:bg-indigo-500/20 text-gray-400 hover:text-indigo-400 transition-colors" title="Laravel Telescope">
                    <i class="fa-solid fa-satellite-dish text-lg"></i>
                </a>
                @endif
            </div>

            <!-- Right: Performance Stats & Close -->
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-3 text-gray-400">
                    <span title="Database queries">
                        <i class="fa-solid fa-database text-violet-400"></i>
                        {{ $queryCount }}q
                    </span>
                    <span title="Page load time">
                        <i class="fa-solid fa-clock text-violet-400"></i>
                        {{ $loadTime }}ms
                    </span>
                    <span title="Peak memory usage">
                        <i class="fa-solid fa-memory text-violet-400"></i>
                        {{ $memoryUsage }}MB
                    </span>
                </div>

                <button
                    @click="$el.closest('.fixed').classList.add('hidden')"
                    class="flex items-center justify-center w-6 h-6 bg-gray-700/50 hover:bg-red-500/30 hover:text-red-400 rounded transition-colors"
                    title="Hide dev bar (refresh to restore)"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add bottom padding to content when dev bar is visible -->
<style>
    body { padding-bottom: 2.75rem; }
    [x-cloak] { display: none !important; }
</style>
@endif