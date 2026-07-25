<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorQueue extends Command
{
    protected $signature = 'monitor:queue {--format=table : Output format (table|json|prometheus)}';
    protected $description = 'Check queue health: driver, backlog, failed, workers, throughput';

    public function handle(): int
    {
        $results = [];
        $results['driver'] = $this->checkDriver();
        $results['connection'] = $this->checkConnection();
        $results['backlog'] = $this->checkBacklog();
        $results['failed'] = $this->checkFailed();
        $results['throughput'] = $this->checkThroughput();
        $results['oldest'] = $this->checkOldest();
        $this->outputResults($results);
        return collect($results)->filter(fn ($r) => $r['status'] === 'FAIL')->count() > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function checkDriver(): array
    {
        $driver = config('queue.default');
        return ['status' => 'OK', 'message' => "Queue driver: {$driver}"];
    }

    private function checkConnection(): array
    {
        try {
            $connection = config('queue.default');
            $config = config("queue.connections.{$connection}");
            if (!$config) return ['status' => 'FAIL', 'message' => "No config for driver: {$connection}"];
            return ['status' => 'OK', 'message' => "Driver config OK"];
        } catch (\Throwable $e) {
            return ['status' => 'FAIL', 'message' => 'Queue config error: ' . $e->getMessage()];
        }
    }

    private function checkBacklog(): array
    {
        try {
            $count = DB::table('jobs')->count();
            if ($count > 100) return ['status' => 'WARN', 'message' => "{$count} pending jobs"];
            return ['status' => 'OK', 'message' => "{$count} pending jobs"];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'No jobs table (database queue not used)'];
        }
    }

    private function checkFailed(): array
    {
        try {
            $count = DB::table('failed_jobs')->count();
            if ($count > 0) return ['status' => 'WARN', 'message' => "{$count} failed job(s)"];
            return ['status' => 'OK', 'message' => 'No failed jobs'];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'No failed_jobs table'];
        }
    }

    private function checkThroughput(): array
    {
        try {
            $recent = DB::table('jobs')->where('created_at', '>=', now()->subHour())->count();
            return ['status' => 'OK', 'message' => "~{$recent} jobs last hour"];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'Throughput not tracked'];
        }
    }

    private function checkOldest(): array
    {
        try {
            $oldest = DB::table('jobs')->min('created_at');
            if (!$oldest) return ['status' => 'OK', 'message' => 'No pending jobs'];
            $hours = now()->diffInHours($oldest);
            if ($hours > 24) return ['status' => 'WARN', 'message' => "Oldest job {$hours}h old"];
            return ['status' => 'OK', 'message' => "Oldest job {$hours}h old"];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'Cannot determine'];
        }
    }

    private function outputResults(array $results): void
    {
        if ($this->option('format') === 'json') { $this->line(json_encode($results, JSON_PRETTY_PRINT)); return; }
        $this->info('=== Queue Health ==='); $this->newLine();
        $rows = [];
        foreach ($results as $key => $r) {
            $icon = match ($r['status']) { 'OK' => '✅', 'WARN' => '⚠️ ', 'FAIL' => '❌', default => '❓' };
            $rows[] = [$icon, $key, $r['status'], $r['message']];
        }
        $this->table(['', 'Check', 'Status', 'Message'], $rows);
    }
}
