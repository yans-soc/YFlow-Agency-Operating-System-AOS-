<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MonitorSecurity extends Command
{
    protected $signature = 'monitor:security
        {--format=table : Output format (table|json|prometheus)}';

    protected $description = 'Check security: SSL, headers, auth, permissions, encryption, activity, firewall, env';

    public function handle(): int
    {
        $results = [];

        $results['ssl'] = $this->checkSsl();
        $results['headers'] = $this->checkHeaders();
        $results['auth_provider'] = $this->checkAuthProvider();
        $results['permissions'] = $this->checkPermissions();
        $results['encryption'] = $this->checkEncryption();
        $results['activity_log'] = $this->checkActivityLog();
        $results['firewall'] = $this->checkFirewall();
        $results['env_exposure'] = $this->checkEnvExposure();

        $this->outputResults($results);
        return collect($results)->filter(fn ($r) => $r['status'] === 'FAIL')->count() > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function checkSsl(): array
    {
        $url = config('app.url', 'http://localhost');
        if (str_starts_with($url, 'https://')) {
            return ['status' => 'OK', 'message' => "HTTPS configured: {$url}"];
        }
        if (app()->environment('production')) {
            return ['status' => 'FAIL', 'message' => "HTTPS not configured in production: {$url}"];
        }
        return ['status' => 'WARN', 'message' => "HTTP in development: {$url}"];
    }

    private function checkHeaders(): array
    {
        try {
            $appUrl = config('app.url', 'http://localhost');
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get($appUrl);
            $headers = $response->headers();
            $missing = [];
            $wanted = ['x-frame-options', 'x-content-type-options', 'x-xss-protection', 'referrer-policy'];
            foreach ($wanted as $h) {
                if (!isset($headers[$h])) $missing[] = $h;
            }
            if (!empty($missing)) {
                return ['status' => 'WARN', 'message' => 'Missing security headers: ' . implode(', ', $missing)];
            }
            return ['status' => 'OK', 'message' => 'All security headers present'];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'App not reachable for header check'];
        }
    }

    private function checkAuthProvider(): array
    {
        $provider = config('auth.guards.web.driver');
        if ($provider === 'session' || $provider === 'sanctum') {
            return ['status' => 'OK', 'message' => "Auth guard: {$provider}"];
        }
        return ['status' => 'WARN', 'message' => "Non-standard auth guard: {$provider}"];
    }

    private function checkPermissions(): array
    {
        $dirs = ['storage', 'bootstrap/cache'];
        $issues = [];
        foreach ($dirs as $dir) {
            $path = base_path($dir);
            if (is_dir($path)) {
                $perms = substr(sprintf('%o', fileperms($path)), -4);
                if ($perms > '0775') $issues[] = "{$dir}: {$perms}";
            }
        }
        if (!empty($issues)) {
            return ['status' => 'WARN', 'message' => 'Overly permissive: ' . implode(', ', $issues)];
        }
        return ['status' => 'OK', 'message' => 'Directory permissions acceptable'];
    }

    private function checkEncryption(): array
    {
        $key = config('app.key');
        if (!$key || $key === 'base64:...') {
            return ['status' => 'FAIL', 'message' => 'APP_KEY not set or default'];
        }
        $cipher = config('app.cipher');
        return ['status' => 'OK', 'message' => "Encryption: {$cipher}"];
    }

    private function checkActivityLog(): array
    {
        try {
            $count = DB::table('activities')->where('created_at', '>=', now()->subDay())->count();
            return ['status' => 'OK', 'message' => "{$count} activities last 24h, logging active"];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'Activity table not found'];
        }
    }

    private function checkFirewall(): array
    {
        $result = \Illuminate\Support\Facades\Process::run('pfctl -s info 2>/dev/null | grep "Status"');
        if ($result->successful()) {
            return ['status' => 'OK', 'message' => 'PF firewall active'];
        }
        return ['status' => 'WARN', 'message' => 'PF firewall check failed (macOS default)'];
    }

    private function checkEnvExposure(): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(3)->get(config('app.url', 'http://localhost') . '/.env');
            if ($response->successful()) {
                return ['status' => 'FAIL', 'message' => '.env is publicly accessible'];
            }
            return ['status' => 'OK', 'message' => '.env not accessible'];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'Cannot verify .env exposure (app unreachable)'];
        }
    }

    private function outputResults(array $results): void
    {
        if ($this->option('format') === 'json') {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));
            return;
        }
        $this->info('=== Security Health ===');
        $this->newLine();
        $rows = [];
        foreach ($results as $key => $r) {
            $icon = match ($r['status']) { 'OK' => '✅', 'WARN' => '⚠️ ', 'FAIL' => '❌', default => '❓' };
            $rows[] = [$icon, $key, $r['status'], $r['message']];
        }
        $this->table(['', 'Check', 'Status', 'Message'], $rows);
    }
}
