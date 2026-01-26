<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Redis;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Deployments & System Status')]
class Deployments extends Component
{
    public bool $refreshing = false;

    public function mount(): void
    {
        $this->checkHadesAccess();
    }

    #[Computed]
    public function services(): array
    {
        return Cache::remember('admin.deployments.services', 60, function () {
            return [
                $this->checkDatabase(),
                $this->checkRedis(),
                $this->checkQueue(),
                $this->checkStorage(),
            ];
        });
    }

    #[Computed]
    public function gitInfo(): array
    {
        return Cache::remember('admin.deployments.git', 300, function () {
            $info = [
                'branch' => 'unknown',
                'commit' => 'unknown',
                'message' => 'unknown',
                'author' => 'unknown',
                'date' => null,
            ];

            try {
                // Get current branch
                $branchResult = Process::path(base_path())->run('git rev-parse --abbrev-ref HEAD');
                if ($branchResult->successful()) {
                    $info['branch'] = trim($branchResult->output());
                }

                // Get latest commit info
                $commitResult = Process::path(base_path())->run('git log -1 --format="%H|%s|%an|%ai"');
                if ($commitResult->successful()) {
                    $parts = explode('|', trim($commitResult->output()));
                    if (count($parts) >= 4) {
                        $info['commit'] = substr($parts[0], 0, 8);
                        $info['message'] = $parts[1];
                        $info['author'] = $parts[2];
                        $info['date'] = \Carbon\Carbon::parse($parts[3])->diffForHumans();
                    }
                }
            } catch (\Exception $e) {
                // Git not available or not a git repo
            }

            return $info;
        });
    }

    #[Computed]
    public function recentCommits(): array
    {
        return Cache::remember('admin.deployments.commits', 300, function () {
            $commits = [];

            try {
                $result = Process::path(base_path())->run('git log -10 --format="%H|%s|%an|%ai"');
                if ($result->successful()) {
                    foreach (explode("\n", trim($result->output())) as $line) {
                        $parts = explode('|', $line);
                        if (count($parts) >= 4) {
                            $commits[] = [
                                'hash' => substr($parts[0], 0, 8),
                                'message' => \Illuminate\Support\Str::limit($parts[1], 60),
                                'author' => $parts[2],
                                'date' => \Carbon\Carbon::parse($parts[3])->diffForHumans(),
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Git not available
            }

            return $commits;
        });
    }

    #[Computed]
    public function stats(): array
    {
        return [
            [
                'label' => 'Database',
                'value' => $this->checkDatabase()['status'] === 'healthy' ? 'Online' : 'Offline',
                'icon' => 'circle-stack',
                'color' => $this->checkDatabase()['status'] === 'healthy' ? 'green' : 'red',
            ],
            [
                'label' => 'Redis',
                'value' => $this->checkRedis()['status'] === 'healthy' ? 'Online' : 'Offline',
                'icon' => 'bolt',
                'color' => $this->checkRedis()['status'] === 'healthy' ? 'green' : 'red',
            ],
            [
                'label' => 'Queue',
                'value' => $this->checkQueue()['status'] === 'healthy' ? 'Active' : 'Inactive',
                'icon' => 'queue-list',
                'color' => $this->checkQueue()['status'] === 'healthy' ? 'green' : 'amber',
            ],
            [
                'label' => 'Storage',
                'value' => $this->checkStorage()['details']['free'] ?? 'N/A',
                'icon' => 'server',
                'color' => 'blue',
            ],
        ];
    }

    public function refresh(): void
    {
        $this->refreshing = true;

        Cache::forget('admin.deployments.services');
        Cache::forget('admin.deployments.git');
        Cache::forget('admin.deployments.commits');

        // Force recompute
        unset($this->services);
        unset($this->gitInfo);
        unset($this->recentCommits);
        unset($this->stats);

        $this->refreshing = false;
        $this->dispatch('notify', message: 'System status refreshed');
    }

    public function clearCache(): void
    {
        Cache::flush();
        $this->dispatch('notify', message: 'Application cache cleared');
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $version = DB::selectOne('SELECT VERSION() as version');

            return [
                'name' => 'Database (MariaDB)',
                'status' => 'healthy',
                'icon' => 'circle-stack',
                'details' => [
                    'version' => $version->version ?? 'Unknown',
                    'connection' => config('database.default'),
                    'database' => config('database.connections.'.config('database.default').'.database'),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'Database (MariaDB)',
                'status' => 'unhealthy',
                'icon' => 'circle-stack',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkRedis(): array
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info();

            return [
                'name' => 'Redis',
                'status' => 'healthy',
                'icon' => 'bolt',
                'details' => [
                    'version' => $info['redis_version'] ?? 'Unknown',
                    'memory' => $info['used_memory_human'] ?? 'Unknown',
                    'clients' => $info['connected_clients'] ?? 0,
                    'uptime' => isset($info['uptime_in_days']) ? $info['uptime_in_days'].' days' : 'Unknown',
                ],
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'Redis',
                'status' => 'unhealthy',
                'icon' => 'bolt',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            return [
                'name' => 'Queue Workers',
                'status' => 'healthy',
                'icon' => 'queue-list',
                'details' => [
                    'driver' => config('queue.default'),
                    'pending' => $pendingJobs,
                    'failed' => $failedJobs,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'Queue Workers',
                'status' => 'unknown',
                'icon' => 'queue-list',
                'error' => 'Could not check queue status',
            ];
        }
    }

    private function checkStorage(): array
    {
        $storagePath = storage_path();
        $freeBytes = disk_free_space($storagePath);
        $totalBytes = disk_total_space($storagePath);

        $freeGb = $freeBytes ? round($freeBytes / 1024 / 1024 / 1024, 1) : 0;
        $totalGb = $totalBytes ? round($totalBytes / 1024 / 1024 / 1024, 1) : 0;
        $usedPercent = $totalBytes ? round((($totalBytes - $freeBytes) / $totalBytes) * 100) : 0;

        return [
            'name' => 'Storage',
            'status' => $usedPercent < 90 ? 'healthy' : 'warning',
            'icon' => 'server',
            'details' => [
                'free' => $freeGb.' GB',
                'total' => $totalGb.' GB',
                'used_percent' => $usedPercent.'%',
            ],
        ];
    }

    private function checkHadesAccess(): void
    {
        if (! auth()->user()?->isHades()) {
            abort(403, 'Hades access required');
        }
    }

    public function render(): View
    {
        return view('hub::admin.deployments')
            ->layout('hub::admin.layouts.app', ['title' => 'Deployments & System Status']);
    }
}
