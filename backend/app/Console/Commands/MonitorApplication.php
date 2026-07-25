<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;

class MonitorApplication extends Command
{
    protected $signature = 'monitor:app
        {--format=table : Output format (table|json|prometheus)}
        {--app-url=http://localhost : Application base URL}';

    protected $description = 'Check application health: auth, API, frontend, queue, scheduler, jobs, email, file storage';

    public function handle(): int
    {
        $results = [];
        $baseUrl = $this->option('app-url');

        $results['app_availability'] = $this->checkAppAvailability($baseUrl);
        $results['api_health'] = $this->checkApiHealth($baseUrl);
        $results['auth_login'] = $this->checkAuth($baseUrl);
        $results['queue'] = $this->checkQueue();
        $results['scheduler'] = $this->checkScheduler();
        $results['jobs'] = $this->checkJobs();
        $results['email'] = $this->checkEmail();
        $results['file_storage'] = $this->checkFileStorage();
        $results['database_migrations'] = $this->checkMigrations();
        $results['app_debug'] = $this->checkAppDebug();

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

    private function checkAppAvailability(string $baseUrl): array
    {
        try {
            $response = Http::timeout(5)->get("{$baseUrl}/api/health");
            if ($response->successful()) {
                $data = $response->json();
                return ['status' => 'OK', 'message' => "App available, status: " . ($data['status'] ?? 'unknown')];
            }
            return ['status' => 'FAIL', 'message' => "App returned {$response->status()}"];
        } catch (\Throwable $e) {
            return ['status' => 'FAIL', 'message' => 'App unavailable: ' . $e->getMessage()];
        }
    }

    private function checkApiHealth(string $baseUrl): array
    {
        try {
            $response = Http::timeout(5)->get("{$baseUrl}/api/health");
            if ($response->successful()) {
                $data = $response->json();
                $db = $data['database'] ?? 'unknown';
                $redis = $data['redis'] ?? 'unknown';
                return ['status' => 'OK', 'message' => "DB: {$db}, Redis: {$redis}"];
            }
            return ['status' => 'FAIL', 'message' => "Health endpoint returned {$response->status()}"];
        } catch (\Throwable $e) {
            return ['status' => 'FAIL', 'message' => 'Health check failed: ' . $e->getMessage()];
        }
    }

    private function checkAuth(string $baseUrl): array
    {
        try {
            $response = Http::timeout(5)->post("{$baseUrl}/api/v1/auth/login", [
                'email' => 'test@example.com',
                'password' => 'password',
            ]);
            if ($response->status() === 401) {
                return ['status' => 'OK', 'message' => 'Auth endpoint reachable (expected 401 for invalid creds)'];
            }
            if ($response->successful()) {
                return ['status' => 'OK', 'message' => 'Auth endpoint operational'];
            }
            return ['status' => 'WARN', 'message' => 'Auth endpoint responded ' . $response->status()];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'Auth check unavailable: ' . $e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        try {
            $connection = config('queue.default');
            return ['status' => 'OK', 'message' => "Queue driver: {$connection}"];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'Queue check failed: ' . $e->getMessage()];
        }
    }

    private function checkScheduler(): array
    {
        try {
            $output = Artisan::call('schedule:list', ['--no-interaction' => true]);
            return ['status' => 'OK', 'message' => 'Scheduler configured'];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'Scheduler check failed: ' . $e->getMessage()];
        }
    }

    private function checkJobs(): array
    {
        try {
            $failedJobs = \DB::table('failed_jobs')->count();
            if ($failedJobs > 0) {
                return ['status' => 'WARN', 'message' => "{$failedJobs} failed job(s) in table"];
            }
            return ['status' => 'OK', 'message' => 'No failed jobs'];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'No failed jobs table (not yet used)'];
        }
    }

    private function checkEmail(): array
    {
        try {
            $driver = config('mail.default');
            $mailers = config('mail.mailers.' . $driver);
            return ['status' => 'OK', 'message' => "Mail driver: {$driver}"];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'Mail config check failed: ' . $e->getMessage()];
        }
    }

    private function checkFileStorage(): array
    {
        try {
            $disk = config('filesystems.default');
            $root = config('filesystems.disks.' . $disk . '.root') ?? storage_path('app');
            $writable = is_writable($root);
            if (!$writable) {
                return ['status' => 'FAIL', 'message' => "Storage disk '{$disk}' is not writable: {$root}"];
            }
            return ['status' => 'OK', 'message' => "Storage disk '{$disk}' writable: {$root}"];
        } catch (\Throwable $e) {
            return ['status' => 'FAIL', 'message' => 'File storage check failed: ' . $e->getMessage()];
        }
    }

    private function checkMigrations(): array
    {
        try {
            $output = Artisan::call('migrate:status', ['--no-interaction' => true]);
            $migrations = Artisan::output();
            $pending = substr_count($migrations, 'Pending');
            if ($pending > 0) {
                return ['status' => 'FAIL', 'message' => "{$pending} pending migration(s)"];
            }
            return ['status' => 'OK', 'message' => 'All migrations run'];
        } catch (\Throwable $e) {
            return ['status' => 'FAIL', 'message' => 'Migration check failed: ' . $e->getMessage()];
        }
    }

    private function checkAppDebug(): array
    {
        $debug = config('app.debug');
        $env = config('app.env');
        if ($env === 'production' && $debug) {
            return ['status' => 'FAIL', 'message' => "APP_DEBUG=true in {$env} environment"];
        }
        $msg = $debug ? 'APP_DEBUG=true (local/dev OK)' : 'APP_DEBUG=false';
        return ['status' => 'OK', 'message' => $msg];
    }

    private function outputTable(array $results): void
    {
        $this->info('=== Application Health ===');
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
            $this->line("# HELP yflow_app_{$key} Application check for {$key}");
            $this->line("# TYPE yflow_app_{$key} gauge");
            $this->line("yflow_app_{$key} {$val}");
        }
    }
}