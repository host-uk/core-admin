<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Analytics</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Privacy-first insights across all your sites</p>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <div class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm text-gray-500 dark:text-gray-400">
                Last 30 days
            </div>
        </div>
    </div>

    <!-- Coming Soon Notice -->
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6 mb-8">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <core:icon name="chart-line" class="text-green-500 w-6 h-6" />
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-medium text-green-800 dark:text-green-200">Coming Soon</h3>
                <p class="mt-1 text-green-700 dark:text-green-300">
                    Analytics integration is on the roadmap. This dashboard will display real-time visitor data, page views, traffic sources, and conversion metrics—all without cookies.
                </p>
            </div>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-12 gap-6 mb-8">
        @foreach($metrics as $metric)
        <div class="col-span-6 sm:col-span-3 bg-white dark:bg-gray-800 shadow-xs rounded-xl p-5">
            <div class="flex items-center mb-2">
                <core:icon name="{{ $metric['icon'] }}" class="text-gray-400 mr-2" />
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $metric['label'] }}</span>
            </div>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $metric['value'] }}</div>
        </div>
        @endforeach
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-12 gap-6">
        @foreach($chartData as $key => $chart)
        <div class="col-span-full {{ $loop->first ? '' : 'lg:col-span-6' }} bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ $chart['title'] }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $chart['description'] }}</p>
            </div>
            <div class="p-5">
                <div class="h-48 bg-gray-50 dark:bg-gray-700/50 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                        <core:icon name="chart-bar" class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
                        <span class="text-sm text-gray-400 dark:text-gray-500">Chart placeholder</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
