<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class MonitorOrchestrator extends Command
{
    protected $signature = 'monitor:all
        {--format=table : Output format (table|json|prometheus)}
        {--report : Generate operational report after checks}
        {--notify : Send notification on failures}';

    protected $description = 'Run all system health checks and generate consolidated report';

    private array $commands = [
        'monitor:infrastructure' => ['--format' => 'json'],
        'monitor:database'       => ['--format' => 'json'],
        'monitor:queue'          => ['--format' => 'json'],
        'monitor:cache'          => ['--format' => 'json'],
        'monitor:network'        => ['--format' => 'json'],
        'monitor:security'       => ['--format' => 'json'],
        'monitor:performance'    => ['--format' => 'json'],
        'monitor:logs'           => ['--format' => 'json', '--lines' => '100'],
        'monitor:app'            => ['--format' => 'json'],
    ];

    public function handle(): int
    {
        $this->info('=== YFlow System Health Check ===');
        $this->newLine();

        $results = [];
        $exitCode = Command::SUCCESS;

        foreach ($this->commands as $command => $args) {
            $this->line("Running: <comment>{$command}</comment>...");

            try {
                $cmdExit = Artisan::call($command, $args);
                $output = json_decode(Artisan::output(), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $output = ['raw' => Artisan::output()];
                }

                $results[$command] = $output;

                if ($cmdExit !== Command::SUCCESS) {
                    $exitCode = Command::FAILURE;
                }
            } catch (\Throwable $e) {
                $results[$command] = [
                    'status' => 'FAIL',
                    'message' => $e->getMessage(),
                ];
                $exitCode = Command::FAILURE;

                Log::channel('operations')->error("Monitor check failed: {$command}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->newLine();
        $this->outputResults($results);

        $summary = $this->getSummary($results);
        $this->newLine();
        $this->info('=== Summary ===');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Checks', $summary['total']],
                ['Passed', $summary['passed']],
                ['Warnings', $summary['warnings']],
                ['Failed', $summary['failed']],
                ['Overall Status', $summary['overall']],
            ]
        );

        Log::channel('operations')->info('System health check completed', $summary);

        if ($this->option('report')) {
            $this->newLine();
            Artisan::call('monitor:report', ['--format' => $this->option('format')]);
            $this->line(Artisan::output());
        }

        return $exitCode;
    }

    private function outputResults(array $results): void
    {
        if ($this->option('format') === 'json') {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));
            return;
        }

        foreach ($results as $command => $data) {
            $commandName = str_replace('monitor:', '', $command);
            $this->line("<fg=cyan>=== {$commandName} ===</fg=cyan>");

            if (isset($data['raw'])) {
                $this->line($data['raw']);
            } elseif (is_array($data)) {
                $rows = [];
                foreach ($data as $key => $check) {
                    if (is_array($check) && isset($check['status'])) {
                        $icon = match ($check['status']) {
                            'OK' => '✅',
                            'WARN' => '⚠️ ',
                            'FAIL' => '❌',
                            default => '❓',
                        };
                        $rows[] = [$icon, $key, $check['status'], $check['message'] ?? ''];
                    }
                }
                if (!empty($rows)) {
                    $this->table(['', 'Check', 'Status', 'Message'], $rows);
                }
            }

            $this->newLine();
        }
    }

    private function getSummary(array $results): array
    {
        $total = 0;
        $passed = 0;
        $warnings = 0;
        $failed = 0;

        foreach ($results as $command => $data) {
            if (!is_array($data)) {
                $failed++;
                $total++;
                continue;
            }
            foreach ($data as $key => $check) {
                if (is_array($check) && isset($check['status'])) {
                    $total++;
                    match ($check['status']) {
                        'OK' => $passed++,
                        'WARN' => $warnings++,
                        'FAIL' => $failed++,
                        default => $failed++,
                    };
                }
            }
        }

        // If no structured data parsed, count command-level status
        if ($total === 0) {
            foreach ($results as $command => $data) {
                if (is_array($data) && isset($data['status'])) {
                    $total++;
                    match ($data['status']) {
                        'OK' => $passed++,
                        'WARN' => $warnings++,
                        'FAIL' => $failed++,
                        default => $failed++,
                    };
                }
            }
        }

        $overall = $failed > 0 ? 'FAIL' : ($warnings > 0 ? 'WARN' : 'OK');

        return [
            'total' => $total,
            'passed' => $passed,
            'warnings' => $warnings,
            'failed' => $failed,
            'overall' => $overall,
        ];
    }
}
