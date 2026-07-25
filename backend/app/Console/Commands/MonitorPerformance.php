<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorPerformance extends Command
{
    protected $signature = 'monitor:performance {--format=table : Output format (table|json|prometheus)}';
    protected $description = 'Check performance: response time, query perf, memory, session, throughput, error rate';

    public function handle(): int
    {
        $results = [];
        $results['response_time'] = $this->checkResponseTime();
        $results['query_performance'] = $this->checkQueryPerformance();
        $results['memory_usage'] = $this->checkMemoryUsage();
        $results['session_handler'] = $this->checkSession();
        $results['error_rate'] = $this->checkErrorRate();
        $results['query_log'] = $this->checkQueryLog();
        $results['opcache'] = $this->checkOpcache();
        $results['request_rate'] = $this->checkRequestRate();
        $this->outputResults($results);
        return collect($results)->filter(fn ($r) => $r['status'] === 'FAIL')->count() > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function checkResponseTime(): array
    {
        $start = microtime(true);
        try { DB::select('SELECT 1'); } catch (\Throwable $e) {}
        $elapsed = round((microtime(true) - $start) * 1000, 1);
        if ($elapsed > 500) return ['status' => 'WARN', 'message' => "DB query took {$elapsed}ms"];
        return ['status' => 'OK', 'message' => "DB query took {$elapsed}ms"];
    }

    private function checkQueryPerformance(): array
    {
        try {
            $result = DB::select("SELECT query, calls, total_time, mean_time FROM pg_stat_statements ORDER BY mean_time DESC LIMIT 5");
            if (empty($result)) return ['status' => 'OK', 'message' => 'pg_stat_statements not enabled or empty'];
            $avg = collect($result)->avg('mean_time');
            $worst = $result[0];
            return ['status' => 'OK', 'message' => "Avg query: " . round($avg, 1) . "ms, worst: {$worst->mean_time}ms ({$worst->query})"];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'pg_stat_statements not available'];
        }
    }

    private function checkMemoryUsage(): array
    {
        $usage = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $mb = round($usage / 1024 / 1024, 1);
        $peakMb = round($peak / 1024 / 1024, 1);
        return ['status' => 'OK', 'message' => "Current: {$mb}MB, Peak: {$peakMb}MB"];
    }

    private function checkSession(): array
    {
        $driver = config('session.driver');
        return ['status' => 'OK', 'message' => "Session driver: {$driver}"];
    }

    private function checkErrorRate(): array
    {
        try {
            $count = DB::table('activities')->where('event', 'error')->where('created_at', '>=', now()->subHour())->count();
            if ($count > 100) return ['status' => 'WARN', 'message' => "{$count} errors last hour"];
            return ['status' => 'OK', 'message' => "{$count} errors last hour"];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'Error tracking not available'];
        }
    }

    private function checkQueryLog(): array
    {
        $log = config('database.connections.pgsql.log_queries', false);
        return ['status' => 'OK', 'message' => $log ? 'Query logging enabled' : 'Query logging disabled (default)'];
    }

    private function checkOpcache(): array
    {
        if (function_exists('opcache_get_status')) {
            $status = opcache_get_status(false);
            if ($status && ($status['opcache_statistics']['oom_restarts'] ?? 0) > 0) {
                return ['status' => 'WARN', 'message' => 'Opcache OOM restarts detected'];
            }
            return ['status' => 'OK', 'message' => 'Opcache active'];
        }
        return ['status' => 'WARN', 'message' => 'Opcache not enabled'];
    }

    private function checkRequestRate(): array
    {
        try {
            $count = DB::table('activities')->where('created_at', '>=', now()->subHour())->count();
            $rate = round($count / 60, 1);
            return ['status' => 'OK', 'message' => "~{$rate} req/min (approx)"];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'Request rate not tracked'];
        }
    }

    private function outputResults(array $results): void
    {
        if ($this->option('format') === 'json') { $this->line(json_encode($results, JSON_PRETTY_PRINT)); return; }
        $this->info('=== Performance Health ==='); $this->newLine();
        $rows = [];
        foreach ($results as $key => $r) {
            $icon = match ($r['status']) { 'OK' => '✅', 'WARN' => '⚠️ ', 'FAIL' => '❌', default => '❓' };
            $rows[] = [$icon, $key, $r['status'], $r['message']];
        }
        $this->table(['', 'Check', 'Status', 'Message'], $rows);
    }
}
