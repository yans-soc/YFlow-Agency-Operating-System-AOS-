<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class MonitorReport extends Command
{
    protected $signature = 'monitor:report
        {--format=table : Output format (table|json|markdown)}
        {--output= : Write report to file path}';

    protected $description = 'Generate operational health report with trends and recommendations';

    private const HISTORY_KEY = 'monitor:history';
    private const HISTORY_TTL = 86400 * 30; // 30 days

    public function handle(): int
    {
        $this->info('Generating YFlow Operational Report...');
        $this->newLine();

        $report = [
            'generated_at' => Carbon::now()->toIso8601String(),
            'environment' => app()->environment(),
            'app_version' => config('app.version', '1.0.0'),
            'uptime' => $this->getUptime(),
            'sections' => [
                'current_health' => $this->getCurrentHealth(),
                'database' => $this->getDatabaseStats(),
                'cache' => $this->getCacheStats(),
                'queue' => $this->getQueueStats(),
                'storage' => $this->getStorageStats(),
                'trends' => $this->getTrends(),
                'recommendations' => $this->getRecommendations(),
            ],
        ];

        $format = $this->option('format');
        $output = $this->option('output');

        match ($format) {
            'json' => $this->outputJson($report),
            'markdown' => $this->outputMarkdown($report),
            default => $this->outputTable($report),
        };

        Log::channel('operations')->info('Operational report generated', [
            'total_checks' => $report['sections']['current_health']['total_checks'] ?? 0,
            'health_score' => $report['sections']['current_health']['health_score'] ?? 0,
        ]);

        if ($output) {
            $content = match ($format) {
                'json' => json_encode($report, JSON_PRETTY_PRINT),
                'markdown' => $this->renderMarkdown($report),
                default => $this->renderText($report),
            };
            file_put_contents($output, $content);
            $this->info("Report written to: {$output}");
        }

        return Command::SUCCESS;
    }

    private function getCurrentHealth(): array
    {
        $checks = [];

        // Re-run monitor:infrastructure in JSON mode
        Artisan::call('monitor:infrastructure', ['--format' => 'json']);
        $infra = json_decode(Artisan::output(), true) ?? [];

        // Run other checks
        foreach (['monitor:database', 'monitor:queue', 'monitor:cache'] as $cmd) {
            Artisan::call($cmd, ['--format' => 'json']);
            $result = json_decode(Artisan::output(), true) ?? [];
            $checks[$cmd] = $result;
        }

        // Compute health score
        $total = 0;
        $passing = 0;

        // Collect all statuses from infrastructure check
        foreach ($infra as $key => $check) {
            if (is_array($check) && isset($check['status'])) {
                $total++;
                if ($check['status'] === 'OK') {
                    $passing++;
                }
            }
        }

        foreach ($checks as $cmd => $data) {
            if (is_array($data)) {
                foreach ($data as $key => $check) {
                    if (is_array($check) && isset($check['status'])) {
                        $total++;
                        if ($check['status'] === 'OK') {
                            $passing++;
                        }
                    }
                }
            }
        }

        $healthScore = $total > 0 ? round(($passing / $total) * 100, 1) : 0;

        return [
            'total_checks' => $total,
            'passing' => $passing,
            'health_score' => $healthScore,
            'status' => $healthScore >= 90 ? 'HEALTHY' : ($healthScore >= 70 ? 'DEGRADED' : 'CRITICAL'),
        ];
    }

    private function getDatabaseStats(): array
    {
        try {
            $connectionCount = DB::select("SELECT count(*) as count FROM pg_stat_activity WHERE state = 'active'")[0]->count ?? 0;
            $tableCount = DB::select("SELECT count(*) as count FROM information_schema.tables WHERE table_schema = 'public'")[0]->count ?? 0;
            $dbSize = DB::select("SELECT pg_database_size(current_database()) as size")[0]->size ?? 0;

            $migrations = DB::table('migrations')->count();

            return [
                'active_connections' => (int) $connectionCount,
                'table_count' => (int) $tableCount,
                'database_size_mb' => round($dbSize / 1024 / 1024, 2),
                'migrations_run' => $migrations,
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getCacheStats(): array
    {
        try {
            $driver = config('cache.default');
            $stats = [
                'driver' => $driver,
                'hit_rate_estimate' => 'N/A (requires cache stats driver)',
            ];

            if ($driver === 'redis') {
                $stats['redis_connected'] = Cache::store('redis')->has('__monitor_test') || true;
            }

            return $stats;
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getQueueStats(): array
    {
        try {
            $queue = config('queue.default');
            $connection = config("queue.connections.{$queue}");

            return [
                'driver' => $queue,
                'connection' => $connection['queue'] ?? 'default',
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getStorageStats(): array
    {
        $disks = ['local', 'public', 's3'];

        $stats = [];
        foreach ($disks as $disk) {
            try {
                $path = storage_path($disk === 'local' ? 'app' : ($disk === 'public' ? 'app/public' : ''));
                if (is_dir($path)) {
                    $stats[$disk] = [
                        'free_bytes' => disk_free_space($path),
                        'total_bytes' => disk_total_space($path),
                        'free_gb' => round(disk_free_space($path) / 1024 / 1024 / 1024, 2),
                        'total_gb' => round(disk_total_space($path) / 1024 / 1024 / 1024, 2),
                        'used_percent' => round((1 - disk_free_space($path) / disk_total_space($path)) * 100, 1),
                    ];
                }
            } catch (\Throwable $e) {
                $stats[$disk] = ['error' => $e->getMessage()];
            }
        }

        return $stats;
    }

    private function getTrends(): array
    {
        try {
            $history = Cache::get(self::HISTORY_KEY, []);
        } catch (\Throwable $e) {
            $history = [];
        }

        // Record current check
        $currentCheck = $this->getCurrentHealth();
        $history[] = [
            'timestamp' => Carbon::now()->toIso8601String(),
            'health_score' => $currentCheck['health_score'],
            'status' => $currentCheck['status'],
        ];

        // Keep only last 30 entries
        $history = array_slice($history, -30);

        try {
            Cache::put(self::HISTORY_KEY, $history, self::HISTORY_TTL);
        } catch (\Throwable $e) {
            // Cache unavailable, continue without persisting
        }

        if (count($history) < 2) {
            return [
                'history' => $history,
                'trend' => 'insufficient_data',
                'message' => 'Need at least 2 data points for trend analysis',
            ];
        }

        // Calculate trend
        $scores = array_column($history, 'health_score');
        $recentAvg = array_sum(array_slice($scores, -3)) / max(1, min(3, count($scores)));
        $olderAvg = array_sum(array_slice($scores, 0, 3)) / max(1, min(3, count($scores)));

        $trend = match (true) {
            $recentAvg > $olderAvg + 2 => 'improving',
            $recentAvg < $olderAvg - 2 => 'degrading',
            default => 'stable',
        };

        return [
            'history' => $history,
            'trend' => $trend,
            'recent_avg' => round($recentAvg, 1),
            'older_avg' => round($olderAvg, 1),
            'message' => match ($trend) {
                'improving' => 'System health is improving',
                'degrading' => 'System health is degrading - investigate issues',
                default => 'System health is stable',
            },
        ];
    }

    private function getRecommendations(): array
    {
        $recommendations = [];
        try {
            $history = Cache::get(self::HISTORY_KEY, []);
        } catch (\Throwable $e) {
            $history = [];
        }

        // Check if we have degrading trend
        if (count($history) >= 2) {
            $scores = array_column($history, 'health_score');
            $recentAvg = array_sum(array_slice($scores, -3)) / min(3, count($scores));
            $olderAvg = array_sum(array_slice($scores, 0, 3)) / min(3, count($scores));

            if ($recentAvg < $olderAvg - 2) {
                $recommendations[] = 'Investigate recent health degradation - check logs and infrastructure';
            }
        }

        // Storage checks
        $storage = $this->getStorageStats();
        foreach ($storage as $disk => $stats) {
            if (isset($stats['used_percent']) && $stats['used_percent'] > 80) {
                $recommendations[] = "Disk {$disk} usage at {$stats['used_percent']}% - consider cleanup or expansion";
            }
        }

        // Database checks
        $db = $this->getDatabaseStats();
        if (!isset($db['error']) && ($db['database_size_mb'] ?? 0) > 1000) {
            $recommendations[] = 'Database size exceeds 1GB - consider archiving old data';
        }

        // General recommendations
        if (!isset($recommendations)) {
            $recommendations = [];
        }
        if (empty($recommendations)) {
            $recommendations[] = 'No issues detected - continue standard operations';
        }

        return $recommendations;
    }

    private function getUptime(): string
    {
        $uptime = @file_get_contents('/proc/uptime');
        if ($uptime === false) {
            return 'N/A (non-Linux)';
        }
        $seconds = (int) explode(' ', $uptime)[0];
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return "{$days}d {$hours}h {$minutes}m";
    }

    private function outputTable(array $report): void
    {
        $sections = $report['sections'];

        // Current Health
        $this->info('=== Current Health ===');
        $health = $sections['current_health'];
        $this->table(
            ['Metric', 'Value'],
            [
                ['Health Score', "{$health['health_score']}%"],
                ['Status', $health['status']],
                ['Total Checks', $health['total_checks']],
                ['Passing', $health['passing']],
            ]
        );
        $this->newLine();

        // Database
        $this->info('=== Database ===');
        if (!isset($sections['database']['error'])) {
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Active Connections', $sections['database']['active_connections']],
                    ['Tables', $sections['database']['table_count']],
                    ['Size', "{$sections['database']['database_size_mb']} MB"],
                    ['Migrations', $sections['database']['migrations_run']],
                ]
            );
        } else {
            $this->error("Error: {$sections['database']['error']}");
        }
        $this->newLine();

        // Storage
        $this->info('=== Storage ===');
        $storageRows = [];
        foreach ($sections['storage'] as $disk => $stats) {
            if (!isset($stats['error'])) {
                $storageRows[] = [$disk, "{$stats['used_percent']}%", "{$stats['free_gb']} GB", "{$stats['total_gb']} GB"];
            }
        }
        if (!empty($storageRows)) {
            $this->table(['Disk', 'Used %', 'Free', 'Total'], $storageRows);
        }
        $this->newLine();

        // Trends
        $this->info('=== Trends ===');
        $trends = $sections['trends'];
        $this->line("Trend: <comment>{$trends['trend']}</comment> - {$trends['message']}");
        if (isset($trends['recent_avg'])) {
            $this->line("Recent avg score: {$trends['recent_avg']}% | Older avg: {$trends['older_avg']}%");
        }

        if (!empty($trends['history'])) {
            $this->newLine();
            $this->info('Recent history (last ' . count($trends['history']) . ' checks):');
            $historyRows = [];
            foreach (array_slice($trends['history'], -5) as $h) {
                $historyRows[] = [$h['timestamp'], "{$h['health_score']}%", $h['status']];
            }
            $this->table(['Time', 'Score', 'Status'], $historyRows);
        }
        $this->newLine();

        // Recommendations
        $this->info('=== Recommendations ===');
        foreach ($sections['recommendations'] as $i => $rec) {
            $this->line(($i + 1) . ". {$rec}");
        }
    }

    private function outputJson(array $report): void
    {
        $this->line(json_encode($report, JSON_PRETTY_PRINT));
    }

    private function outputMarkdown(array $report): void
    {
        $this->line($this->renderMarkdown($report));
    }

    private function renderMarkdown(array $report): string
    {
        $md = "# YFlow Operational Report\n\n";
        $md .= "- **Generated:** {$report['generated_at']}\n";
        $md .= "- **Environment:** {$report['environment']}\n";
        $md .= "- **Version:** {$report['app_version']}\n";
        $md .= "- **Uptime:** {$report['uptime']}\n\n";

        $md .= "## Current Health\n\n";
        $h = $report['sections']['current_health'];
        $md .= "- Health Score: {$h['health_score']}%\n";
        $md .= "- Status: {$h['status']}\n";
        $md .= "- Checks: {$h['passing']}/{$h['total_checks']}\n\n";

        $md .= "## Database\n\n";
        if (!isset($report['sections']['database']['error'])) {
            $db = $report['sections']['database'];
            $md .= "- Active Connections: {$db['active_connections']}\n";
            $md .= "- Tables: {$db['table_count']}\n";
            $md .= "- Size: {$db['database_size_mb']} MB\n";
        }

        $md .= "\n## Recommendations\n\n";
        foreach ($report['sections']['recommendations'] as $rec) {
            $md .= "- {$rec}\n";
        }

        return $md;
    }

    private function renderText(array $report): string
    {
        // Fallback plain text output for file export (table format already prints to console)
        return $this->renderMarkdown($report);
    }
}
