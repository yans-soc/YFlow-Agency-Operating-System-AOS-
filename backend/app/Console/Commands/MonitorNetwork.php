<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MonitorNetwork extends Command
{
    protected $signature = 'monitor:network {--format=table : Output format (table|json|prometheus)}';
    protected $description = 'Check network: latency, DNS, external services, ports, connectivity';

    public function handle(): int
    {
        $results = [];
        $results['app_reachability'] = $this->checkReachability(config('app.url', 'http://localhost'), 'App');
        $results['dns'] = $this->checkDns();
        $results['external_api'] = $this->checkExternalApi();
        $results['port_80'] = $this->checkPort(80);
        $results['port_443'] = $this->checkPort(443);
        $results['internal_latency'] = $this->checkLatency('127.0.0.1');
        $this->outputResults($results);
        return collect($results)->filter(fn ($r) => $r['status'] === 'FAIL')->count() > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function checkReachability(string $url, string $label): array
    {
        try {
            $response = Http::timeout(5)->get($url);
            return ['status' => 'OK', 'message' => "{$label}: {$response->status()}"];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => "{$label} unreachable: {$e->getMessage()}"];
        }
    }

    private function checkDns(): array
    {
        $host = parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST);
        if (!$host) return ['status' => 'FAIL', 'message' => 'No host in APP_URL'];
        $ip = gethostbyname($host);
        if ($ip === $host) return ['status' => 'WARN', 'message' => "DNS: {$host} unresolved"];
        return ['status' => 'OK', 'message' => "DNS: {$host} -> {$ip}"];
    }

    private function checkExternalApi(): array
    {
        try {
            $response = Http::timeout(5)->head('https://api.github.com');
            return ['status' => 'OK', 'message' => 'External HTTP: OK'];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'External HTTP: ' . $e->getMessage()];
        }
    }

    private function checkPort(int $port): array
    {
        $host = '0.0.0.0';
        $conn = @fsockopen($host, $port, $errno, $errstr, 2);
        if ($conn) { fclose($conn); return ['status' => 'OK', 'message' => "Port {$port}: open"]; }
        return ['status' => 'WARN', 'message' => "Port {$port}: closed/unreachable"];
    }

    private function checkLatency(string $host): array
    {
        $result = \Illuminate\Support\Facades\Process::run("ping -c 1 -t 2 {$host} 2>&1");
        if ($result->successful()) {
            preg_match('/time=([0-9.]+)/', $result->output(), $m);
            $ms = $m[1] ?? '?';
            return ['status' => 'OK', 'message' => "Latency to {$host}: {$ms}ms"];
        }
        return ['status' => 'WARN', 'message' => "Cannot ping {$host}"];
    }

    private function outputResults(array $results): void
    {
        if ($this->option('format') === 'json') { $this->line(json_encode($results, JSON_PRETTY_PRINT)); return; }
        $this->info('=== Network Health ==='); $this->newLine();
        $rows = [];
        foreach ($results as $key => $r) {
            $icon = match ($r['status']) { 'OK' => '✅', 'WARN' => '⚠️ ', 'FAIL' => '❌', default => '❓' };
            $rows[] = [$icon, $key, $r['status'], $r['message']];
        }
        $this->table(['', 'Check', 'Status', 'Message'], $rows);
    }
}
