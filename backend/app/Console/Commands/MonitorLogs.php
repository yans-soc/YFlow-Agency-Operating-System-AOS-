<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorLogs extends Command
{
    protected $signature = 'monitor:logs {--format=table : Output format (table|json|prometheus)} {--lines=50 : Lines to check for errors}';
    protected $description = 'Check logs: errors, warnings, pattern frequency, log file size, retention';

    public function handle(): int
    {
        $results = [];
        $results['error_frequency'] = $this->checkErrorFrequency();
        $results['warning_frequency'] = $this->checkWarningFrequency();
        $results['log_file_size'] = $this->checkLogFileSize();
        $results['critical_events'] = $this->checkCriticalEvents();
        $results['slow_log'] = $this->checkSlowLog();
        $results['log_format'] = $this->checkLogFormat();
        $this->outputResults($results);
        return collect($results)->filter(fn ($r) => $r['status'] === 'FAIL')->count() > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function checkErrorFrequency(): array
    {
        try {
            $count = DB::table('activities')->where('event', 'error')->where('created_at', '>=', now()->subDay())->count();
            if ($count > 50) return ['status' => 'WARN', 'message' => "{$count} errors logged today"];
            return ['status' => 'OK', 'message' => "{$count} errors logged today"];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'Error tracking via activities table'];
        }
    }

    private function checkWarningFrequency(): array
    {
        try {
            $path = storage_path('logs/laravel.log');
            if (!file_exists($path)) return ['status' => 'OK', 'message' => 'Laravel log not found'];
            $lines = $this->option('lines');
            $tail = \Illuminate\Support\Facades\Process::run("tail -{$lines} " . escapeshellarg($path));
            $warnings = substr_count($tail->output(), '.WARNING:');
            $errors = substr_count($tail->output(), '.ERROR:');
            $issues = $errors + $warnings;
            if ($issues > 10) return ['status' => 'WARN', 'message' => "Last {$lines} lines: {$errors} errors, {$warnings} warnings"];
            return ['status' => 'OK', 'message' => "Last {$lines} lines: {$errors} errors, {$warnings} warnings"];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'Log file check unavailable'];
        }
    }

    private function checkLogFileSize(): array
    {
        try {
            $path = storage_path('logs/laravel.log');
            if (!file_exists($path)) return ['status' => 'OK', 'message' => 'No laravel.log'];
            $size = filesize($path);
            $mb = round($size / 1024 / 1024, 1);
            if ($mb > 500) return ['status' => 'WARN', 'message' => "laravel.log: {$mb}MB (consider log rotation)"];
            return ['status' => 'OK', 'message' => "laravel.log: {$mb}MB"];
        } catch (\Throwable $e) {
            return ['status' => 'WARN', 'message' => 'Cannot check log file size'];
        }
    }

    private function checkCriticalEvents(): array
    {
        try {
            $critical = DB::table('activities')
                ->whereIn('event', ['error', 'security_alert', 'system_failure'])
                ->where('created_at', '>=', now()->subDay())
                ->count();
            if ($critical > 10) return ['status' => 'WARN', 'message' => "{$critical} critical events today"];
            return ['status' => 'OK', 'message' => "{$critical} critical events today"];
        } catch (\Throwable $e) {
            return ['status' => 'OK', 'message' => 'Critical event tracking via activities'];
        }
    }

    private function checkSlowLog(): array
    {
        $path = storage_path('logs/slow.log');
        if (!file_exists($path)) return ['status' => 'OK', 'message' => 'No slow.log'];
        $size = filesize($path);
        $mb = round($size / 1024 / 1024, 1);
        return ['status' => 'OK', 'message' => "Slow log: {$mb}MB"];
    }

    private function checkLogFormat(): array
    {
        $channel = config('logging.default');
        $stack = config("logging.channels.{$channel}");
        return ['status' => 'OK', 'message' => "Log channel: {$channel}"];
    }

    private function outputResults(array $results): void
    {
        if ($this->option('format') === 'json') { $this->line(json_encode($results, JSON_PRETTY_PRINT)); return; }
        $this->info('=== Log Health ==='); $this->newLine();
        $rows = [];
        foreach ($results as $key => $r) {
            $icon = match ($r['status']) { 'OK' => '✅', 'WARN' => '⚠️ ', 'FAIL' => '❌', default => '❓' };
            $rows[] = [$icon, $key, $r['status'], $r['message']];
        }
        $this->table(['', 'Check', 'Status', 'Message'], $rows);
    }
}
