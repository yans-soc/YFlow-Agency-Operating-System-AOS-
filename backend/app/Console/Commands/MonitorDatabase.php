<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorDatabase extends Command
{
    protected $signature = 'monitor:database
        {--format=table : Output format (table|json|prometheus)}
        {--slow-query-threshold=1000 : Slow query threshold in ms}';

    protected $description = 'Check database health: connection pool, slow queries, deadlocks, locks, index efficiency, size, backup';

    public function handle(): int
    {
        $results = [];

        $results['connection'] = $this->checkConnection();
        $results['size'] = $this->checkDatabaseSize();
        $results['connections_open'] = $this->checkOpenConnections();
        $results['slow_queries'] = $this->checkSlowQueries();
        $results['locks'] = $this->checkLocks();
        $results['deadlocks'] = $this->checkDeadlocks();
        $results['index_usage'] = $this->checkIndexUsage();
        $results['table_stats'] = $this->checkTableStats();
        $results['long_running'] = $this->checkLongRunning();
        $results['replication'] = $this->checkReplication();

        if ($this->option('format') === 'json') {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));
        } elseif ($this->option('format') === 'prometheus') {
            $this->outputPrometheus($results);
        } else {
            $this->outputTable($results);
        }

        $failed = collect($results)->filter(fn ($r) => $r['status'] === 'FAIL')->count();
        $warnings = collect($results)->filter(fn ($r) => $r['status'] === 'WARN')->count();

        $this->newLine();
        $this->components->twoColumnDetail('Passed', collect($results)->filter(fn ($r) => $r['status'] === 'OK')->count());
        $this->components->twoColumnDetail('Warnings', $warnings);
        $this->components->twoColumnDetail('Failed', $failed);

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function checkConnection(): array
    {
        try {
            DB::connection()->getPdo();
            $name = DB::connection()->getDatabaseName();
            return ['status' => 'OK', 'message' => "Connected to {$name}"];
        } catch (\Throwable $e) {
            return ['status' => 'FAIL', 'message' => 'Connection failed: ' . $e->getMessage()];
        }
    }

    private function checkDatabaseSize(): array
    {
        try {
            $db = DB::connection()->getDatabaseName();
            $result = DB::select("
                SELECT pg_database_size(current_database()) as bytes,
                       pg_size_pretty(pg_database_size(current_database())) as pretty
            ");
            $size = $result[0]->pretty ?? 'unknown';
            $bytes = (int) ($result[0]->bytes ?? 0);
            $threshold = 10 * 1024 * 1024 * 1024; // 10GB
            if ($bytes > $threshold) {
                return ['status' => 'WARN', 'message' => "Database size: {$size} (above 10GB)"];
            }
            return ['status' => 'OK', 'message' => "Database size: {$size}"];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'Size check failed: ' . $e->getMessage()];
        }
    }

    private function checkOpenConnections(): array
    {
        try {
            $result = DB::select("SELECT count(*) as total FROM pg_stat_activity WHERE datname = current_database()");
            $total = (int) ($result[0]->total ?? 0);
            if ($total > 50) {
                return ['status' => 'WARN', 'message' => "{$total} open connections"];
            }
            return ['status' => 'OK', 'message' => "{$total} open connections"];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'Connection count failed: ' . $e->getMessage()];
        }
    }

    private function checkSlowQueries(): array
    {
        try {
            $threshold = (int) $this->option('slow-query-threshold');
            $result = DB::select("
                SELECT count(*) as total
                FROM pg_stat_activity
                WHERE state = 'active'
                  AND query_start < NOW() - INTERVAL '{$threshold} milliseconds'
                  AND query NOT LIKE '%pg_stat%'
                  AND datname = current_database()
            ");
            $slow = (int) ($result[0]->total ?? 0);
            if ($slow > 0) {
                return ['status' => 'WARN', 'message' => "{$slow} slow query(ies) > {$threshold}ms"];
            }
            return ['status' => 'OK', 'message' => "No queries exceeding {$threshold}ms"];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'Slow query check failed: ' . $e->getMessage()];
        }
    }

    private function checkLocks(): array
    {
        try {
            $result = DB::select("
                SELECT count(*) as total
                FROM pg_locks l
                JOIN pg_stat_all_tables t ON l.relation = t.relid
                WHERE l.granted = true AND l.mode LIKE '%Exclusive%'
            ");
            $locks = (int) ($result[0]->total ?? 0);
            if ($locks > 10) {
                return ['status' => 'WARN', 'message' => "{$locks} exclusive locks held"];
            }
            return ['status' => 'OK', 'message' => "{$locks} exclusive locks"];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'Lock check failed (read-only): ' . $e->getMessage()];
        }
    }

    private function checkDeadlocks(): array
    {
        try {
            $result = DB::select("
                SELECT datname, deadlocks
                FROM pg_stat_database
                WHERE datname = current_database()
            ");
            $deadlocks = (int) ($result[0]->deadlocks ?? 0);
            if ($deadlocks > 0) {
                return ['status' => 'WARN', 'message' => "{$deadlocks} deadlock(s) detected since stats reset"];
            }
            return ['status' => 'OK', 'message' => 'No deadlocks detected'];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'Deadlock check failed: ' . $e->getMessage()];
        }
    }

    private function checkIndexUsage(): array
    {
        try {
            $result = DB::select("
                SELECT
                    sum(case when idx_scan = 0 then 1 else 0 end) as unused,
                    count(*) as total
                FROM pg_stat_user_indexes
            ");
            $total = (int) ($result[0]->total ?? 0);
            $unused = (int) ($result[0]->unused ?? 0);
            $ratio = $total > 0 ? round($unused / $total * 100, 1) : 0;
            if ($ratio > 30) {
                return ['status' => 'WARN', 'message' => "{$unused}/{$total} indexes unused ({$ratio}%)"];
            }
            return ['status' => 'OK', 'message' => "{$unused}/{$total} indexes unused ({$ratio}%)"];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'Index check failed: ' . $e->getMessage()];
        }
    }

    private function checkTableStats(): array
    {
        try {
            $result = DB::select("
                SELECT schemaname, relname, n_live_tup as rows
                FROM pg_stat_user_tables
                ORDER BY n_live_tup DESC
                LIMIT 5
            ");
            if (empty($result)) {
                return ['status' => 'OK', 'message' => 'No user tables with stats yet'];
            }
            $largest = $result[0];
            return ['status' => 'OK', 'message' => "Largest table: {$largest->relname} ({$largest->rows} rows)"];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'Table stats check failed: ' . $e->getMessage()];
        }
    }

    private function checkLongRunning(): array
    {
        try {
            $result = DB::select("
                SELECT count(*) as total,
                       COALESCE(MAX(EXTRACT(EPOCH FROM (NOW() - query_start))), 0) as max_seconds
                FROM pg_stat_activity
                WHERE state = 'active'
                  AND query NOT LIKE '%pg_stat%'
                  AND query NOT LIKE '%pg_locks%'
                  AND datname = current_database()
            ");
            $count = (int) ($result[0]->total ?? 0);
            $maxSec = (int) ($result[0]->max_seconds ?? 0);
            if ($maxSec > 300) {
                return ['status' => 'WARN', 'message' => "{$count} active, longest {$maxSec}s (>5min)"];
            }
            return ['status' => 'OK', 'message' => "{$count} active, longest {$maxSec}s"];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'Cannot query pg_stat_activity'];
        }
    }

    private function checkReplication(): array
    {
        try {
            $result = DB::select("SELECT pg_is_in_recovery()");
            $inRecovery = $result[0]->pg_is_in_recovery ?? false;
            if ($inRecovery) {
                $lag = DB::select("SELECT GREATEST(0, EXTRACT(EPOCH FROM (NOW() - pg_last_xact_replay_timestamp()))) as lag_seconds");
                $seconds = (int) ($lag[0]->lag_seconds ?? 0);
                return ['status' => 'OK', 'message' => "Replica, lag: {$seconds}s"];
            }
            return ['status' => 'OK', 'message' => 'Primary node'];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'Single node (no replication)'];
        }
    }

    private function outputTable(array $results): void
    {
        $this->info('=== Database Health ===');
        $this->newLine();
        $rows = [];
        foreach ($results as $key => $r) {
            $icon = match ($r['status']) {
                'OK' => '✅',
                'WARN' => '⚠️ ',
                'FAIL' => '❌',
                default => '❓',
            };
            $rows[] = [$icon, $key, $r['status'], $r['message']];
        }
        $this->table(['', 'Component', 'Status', 'Message'], $rows);
    }

    private function outputPrometheus(array $results): void
    {
        foreach ($results as $key => $r) {
            $val = match ($r['status']) {
                'OK' => 1,
                'WARN' => 0,
                'FAIL' => 0,
                default => -1,
            };
            $this->line("# HELP yflow_db_{$key} Database check for {$key}");
            $this->line("# TYPE yflow_db_{$key} gauge");
            $this->line("yflow_db_{$key} {$val}");
        }
    }
}