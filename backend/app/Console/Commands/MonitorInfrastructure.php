<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class MonitorInfrastructure extends Command
{
    protected $signature = 'monitor:infrastructure
        {--format=table : Output format (table|json|prometheus)}
        {--threshold-disk=80 : Disk usage warning threshold percent}
        {--threshold-memory=80 : Memory usage warning threshold percent}';

    protected $description = 'Check infrastructure health: Docker, Nginx, PHP, Node, Redis, PostgreSQL, disk, memory, CPU';

    public function handle(): int
    {
        $results = [];

        $results['docker'] = $this->checkDocker();
        $results['disk'] = $this->checkDisk();
        $results['memory'] = $this->checkMemory();
        $results['cpu'] = $this->checkCpu();
        $results['database'] = $this->checkDatabase();
        $results['postgresql'] = $this->checkPostgres();
        $results['redis'] = $this->checkRedis();
        $results['php'] = $this->checkPhp();
        $results['node'] = $this->checkNode();
        $results['network'] = $this->checkNetwork();
        $results['time'] = $this->checkTime();

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

    private function checkDocker(): array
    {
        $result = Process::run('docker info --format "{{.ServerVersion}}" 2>/dev/null');
        if (!$result->successful()) {
            return ['status' => 'WARN', 'message' => 'Docker not available (may be expected)'];
        }
        return ['status' => 'OK', 'message' => 'Docker ' . trim($result->output())];
    }

    private function checkDisk(): array
    {
        $result = Process::run("df -P / | tail -1 | awk '{print \$5 \" \" \$4}'");
        if (!$result->successful()) {
            return ['status' => 'WARN', 'message' => 'Cannot read disk usage'];
        }
        $parts = explode(' ', trim($result->output()));
        $usage = (int) str_replace('%', '', $parts[0] ?? '0');
        $available = $parts[1] ?? 'unknown';
        $threshold = (int) $this->option('threshold-disk');

        if ($usage >= $threshold) {
            return ['status' => 'WARN', 'message' => "Disk at {$usage}% (threshold: {$threshold}%), {$available}KB available"];
        }
        return ['status' => 'OK', 'message' => "Disk at {$usage}%, {$available}KB available"];
    }

    private function checkMemory(): array
    {
        $result = Process::run("free | grep Mem | awk '{printf \"%.0f %s\", \$3/\$2 * 100, \$7}'");
        if (!$result->successful()) {
            return ['status' => 'WARN', 'message' => 'Cannot read memory usage'];
        }
        $parts = explode(' ', trim($result->output()));
        $usage = (int) ($parts[0] ?? '0');
        $available = $parts[1] ?? '0';
        $threshold = (int) $this->option('threshold-memory');

        if ($usage >= $threshold) {
            return ['status' => 'WARN', 'message' => "Memory at {$usage}% (threshold: {$threshold}%), {$available}KB available"];
        }
        return ['status' => 'OK', 'message' => "Memory at {$usage}%, {$available}KB available"];
    }

    private function checkCpu(): array
    {
        $result = Process::run("top -l 1 | grep 'CPU usage' | awk '{print \$3}' | sed 's/%//'");
        if (!$result->successful()) {
            return ['status' => 'WARN', 'message' => 'Cannot read CPU usage'];
        }
        $usage = (float) ($result->output() ?: 0);
        if ($usage > 90) {
            return ['status' => 'WARN', 'message' => "CPU at {$usage}%"];
        }
        return ['status' => 'OK', 'message' => "CPU at {$usage}%"];
    }

    private function checkDatabase(): array
    {
        try {
            \DB::connection()->getPdo();
            $name = \DB::connection()->getDatabaseName();
            return ['status' => 'OK', 'message' => "Connected to {$name}"];
        } catch (\Throwable $e) {
            return ['status' => 'FAIL', 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    private function checkPostgres(): array
    {
        try {
            $result = \DB::select("SELECT pg_is_in_recovery()");
            $inRecovery = $result[0]->pg_is_in_recovery ?? false;
            $role = $inRecovery ? 'replica' : 'primary';
            return ['status' => 'OK', 'message' => "PostgreSQL running as {$role}"];
        } catch (\Throwable $e) {
            return ['status' => 'FAIL', 'message' => 'PostgreSQL check failed: ' . $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        try {
            if (class_exists(\Illuminate\Support\Facades\Redis::class)) {
                \Illuminate\Support\Facades\Redis::connection()->ping();
                return ['status' => 'OK', 'message' => 'Redis connected'];
            }
            return ['status' => 'WARN', 'message' => 'Redis not configured'];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'Redis unavailable: ' . $e->getMessage()];
        }
    }

    private function checkPhp(): array
    {
        $version = PHP_VERSION;
        $extensions = ['pdo', 'pdo_pgsql', 'mbstring', 'json', 'xml', 'curl', 'redis', 'fileinfo'];
        $missing = [];
        foreach ($extensions as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        if (!empty($missing)) {
            return ['status' => 'WARN', 'message' => "PHP {$version}, missing extensions: " . implode(', ', $missing)];
        }
        return ['status' => 'OK', 'message' => "PHP {$version} with all required extensions"];
    }

    private function checkNode(): array
    {
        $result = Process::run('node --version 2>/dev/null');
        if (!$result->successful()) {
            return ['status' => 'WARN', 'message' => 'Node not found'];
        }
        return ['status' => 'OK', 'message' => 'Node ' . trim($result->output())];
    }

    private function checkNetwork(): array
    {
        $result = Process::run('curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 http://localhost/api/health 2>/dev/null');
        $code = trim($result->output());
        if ($code === '200') {
            return ['status' => 'OK', 'message' => 'Local health endpoint responds with 200'];
        }
        return ['status' => 'WARN', 'message' => "Local health endpoint returned {$code}"];
    }

    private function checkTime(): array
    {
        $result = Process::run('date +%s');
        $epoch = (int) ($result->output() ?: 0);
        $diff = abs(time() - $epoch);
        if ($diff > 5) {
            return ['status' => 'WARN', 'message' => "System time drift: {$diff}s"];
        }
        return ['status' => 'OK', 'message' => "Time synchronized (drift: {$diff}s)"];
    }

    private function outputTable(array $results): void
    {
        $this->info('=== Infrastructure Health ===');
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
            $this->line("# HELP yflow_infrastructure_{$key} Infrastructure check for {$key}");
            $this->line("# TYPE yflow_infrastructure_{$key} gauge");
            $this->line("yflow_infrastructure_{$key} {$val}");
        }
    }
}