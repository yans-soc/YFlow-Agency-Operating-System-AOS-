<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MonitorCache extends Command
{
    protected $signature = 'monitor:cache {--format=table : Output format (table|json|prometheus)}';

    protected $description = 'Check cache health: hit rate, store, memory, key count, accessibility';

    public function handle(): int
    {
        $results = [];
        $results['store'] = $this->checkStore();
        $results['connection'] = $this->checkConnection();
        $results['hit_miss'] = $this->checkHitMiss();
        $results['memory'] = $this->checkMemory();
        $results['key_count'] = $this->checkKeyCount();

        $this->outputResults($results);
        return collect($results)->filter(fn ($r) => $r['status'] === 'FAIL')->count() > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function checkStore(): array
    {
        $driver = config('cache.default');
        return ['status' => 'OK', 'message' => "Cache driver: {$driver}"];
    }

    private function checkConnection(): array
    {
        try {
            Cache::store()->put('yflow:monitor:ping', true, 1);
            $val = Cache::store()->get('yflow:monitor:ping');
            if ($val) {
                Cache::store()->forget('yflow:monitor:ping');
                return ['status' => 'OK', 'message' => 'Cache read/write successful'];
            }
            return ['status' => 'FAIL', 'message' => 'Cache read/write mismatch'];
        } catch (\Throwable $e) {
            return ['status' => 'FAIL', 'message' => 'Cache unavailable: ' . $e->getMessage()];
        }
    }

    private function checkHitMiss(): array
    {
        try {
            $hits = Cache::store()->get('yflow:hits', 0);
            $misses = Cache::store()->get('yflow:misses', 0);
            $total = $hits + $misses;
            if ($total === 0) return ['status' => 'OK', 'message' => 'No cache stats recorded yet'];
            $rate = round($hits / $total * 100, 1);
            if ($rate < 50) return ['status' => 'WARN', 'message' => "Hit rate: {$rate}% ({$hits}/{$total})"];
            return ['status' => 'OK', 'message' => "Hit rate: {$rate}% ({$hits}/{$total})"];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'Hit rate not tracked'];
        }
    }

    private function checkMemory(): array
    {
        try {
            $result = \Illuminate\Support\Facades\Process::run('ps aux | grep -E "redis|memcached" | grep -v grep | head -1');
            if ($result->successful()) {
                $mem = \Illuminate\Support\Facades\Process::run("ps -o rss= -p \$(pgrep -f 'redis-server|memcached' | head -1) 2>/dev/null");
                $kb = (int) trim($mem->output());
                return ['status' => 'OK', 'message' => 'Cache server running, ' . round($kb / 1024, 1) . 'MB RSS'];
            }
            return ['status' => 'WARN', 'message' => 'Could not determine cache memory'];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'File/database cache (no memory process)'];
        }
    }

    private function checkKeyCount(): array
    {
        try {
            $result = Cache::store()->get('yflow:key:count', null);
            if ($result === null) return ['status' => 'OK', 'message' => 'Key tracking not enabled'];
            return ['status' => 'OK', 'message' => "Stored keys: {$result}"];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'Key count not tracked'];
        }
    }

    private function outputResults(array $results): void
    {
        if ($this->option('format') === 'json') {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));
            return;
        }
        $this->info('=== Cache Health ===');
        $this->newLine();
        $rows = [];
        foreach ($results as $key => $r) {
            $icon = match ($r['status']) { 'OK' => '✅', 'WARN' => '⚠️ ', 'FAIL' => '❌', default => '❓' };
            $rows[] = [$icon, $key, $r['status'], $r['message']];
        }
        $this->table(['', 'Check', 'Status', 'Message'], $rows);
    }
}
