<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Commands;

use App\Services\FileDiscovery;
use App\Services\TaskIndex;
use LaravelZero\Framework\Commands\Command;

use function implode;
use function str_repeat;

final class ShowCommand extends Command
{
    private const array STATUS_SYMBOLS = [
        'open' => '☐',
        'checked' => '☑',
        'ongoing' => '⊙',
        'obsolete' => '⊘',
        'inquestion' => '?',
    ];

    protected $signature = 'show
                           {--f|file= : Specific .xit file to show}
                           {--status= : Filter by status (open, checked, ongoing, obsolete, inquestion)}
                           {--show-id : Show task IDs}
                           {--subdir : Include subdirectories}';

    protected $description = 'Display tasks from .xit files';

    public function handle(): int
    {
        $files = FileDiscovery::findXitFiles(
            $this->option('file'),
            $this->option('subdir'),
        );

        // @codeCoverageIgnoreStart
        if (empty($files)) {
            $this->info('No .xit files found');

            return self::SUCCESS;
        }
        // @codeCoverageIgnoreEnd

        $index = new TaskIndex($files);
        $tasks = $index->getAllTasks();

        if (empty($tasks)) {
            $this->info('No tasks found');

            return self::SUCCESS;
        }

        $statusFilter = $this->option('status');
        $showId = $this->option('show-id');
        $currentFile = '';

        foreach ($tasks as $i => $taskRef) {
            if ($statusFilter && $taskRef->task->status !== $statusFilter) {
                continue;
            }

            if ($taskRef->file->path !== $currentFile) {
                // @codeCoverageIgnoreStart
                if ($currentFile !== '') {
                    $this->newLine();
                }
                // @codeCoverageIgnoreEnd

                $currentFile = $taskRef->file->path;
                $this->line("<fg=white;options=bold>{$currentFile}</>");
            }

            $id = $i + 1;
            $idStr = $showId ? "<fg=gray>[{$id}]</> " : '';
            $statusSymbol = self::STATUS_SYMBOLS[$taskRef->task->status];
            $color = $this->getStatusColor($taskRef->task->status);
            $priority = str_repeat('!', $taskRef->task->priority);
            $priorityStr = $priority ? "{$priority} " : '';

            $description = $taskRef->task->description;

            // @codeCoverageIgnoreStart
            if (!empty($taskRef->task->continuationLines)) {
                $description .= ' '.implode(' ', $taskRef->task->continuationLines);
            }
            // @codeCoverageIgnoreEnd

            $this->line("{$idStr}<fg={$color}>{$statusSymbol}</> {$priorityStr}{$description}");
        }

        return self::SUCCESS;
    }

    private function getStatusColor(string $status): string
    {
        // @codeCoverageIgnoreStart
        return match ($status) {
            'open' => 'gray',
            'checked' => 'green',
            'ongoing' => 'yellow',
            'obsolete' => 'gray',
            'inquestion' => 'magenta',
            default => 'default',
        };
        // @codeCoverageIgnoreEnd
    }
}
