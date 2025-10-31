<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Commands;

use App\Parser\DueDate;
use App\Parser\Task;
use App\Parser\TaskGroup;
use App\Parser\XitParser;
use App\Services\DateParser;
use App\Services\FileDiscovery;
use App\Services\TaskIndex;
use DateTime;
use LaravelZero\Framework\Commands\Command;

use function array_merge;
use function count;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function mb_strtolower;
use function preg_match;
use function preg_replace;
use function realpath;
use function str_contains;

final class RecurCommand extends Command
{
    protected $signature = 'recur
                           {id : Task ID to create recurring instances from}
                           {--interval= : Recurrence interval (e.g., 1d, 1w, 2w, 1m, 1y)}
                           {--count= : Number of instances to create}
                           {--end= : End date for recurrence}
                           {--target= : Target file for recurring instances}
                           {--f|file= : Source .xit file}';

    protected $description = 'Create recurring task instances';

    public function handle(): int
    {
        $taskId = (int) $this->argument('id');
        $interval = $this->option('interval');
        $count = $this->option('count') ? (int) $this->option('count') : null;
        $end = $this->option('end');
        $target = $this->option('target');

        if (!$interval) {
            $this->error('Interval is required (use --interval)');

            return self::FAILURE;
        }

        if (!$count && !$end) {
            $this->error('Either --count or --end must be specified');

            return self::FAILURE;
        }

        if (!preg_match('/^(\d+)([dwmy])$/i', $interval, $intervalMatch)) {
            $this->error("Invalid interval format: {$interval}");

            return self::FAILURE;
        }

        $amount = (int) $intervalMatch[1];
        $unit = mb_strtolower($intervalMatch[2]);

        $files = FileDiscovery::findXitFiles($this->option('file'));
        $index = new TaskIndex($files);
        $taskRef = $index->getTaskByIndex($taskId);

        // @codeCoverageIgnoreStart
        if (!$taskRef) {
            $this->error("Task {$taskId} not found");

            return self::FAILURE;
        }
        // @codeCoverageIgnoreEnd

        // @codeCoverageIgnoreStart
        $currentDate = $taskRef->task->dueDate?->date ?? new DateTime();
        $instances = 0;
        $maxInstances = $count ?? 1_000;
        $endDate = $end ? DateParser::parse($end) : null;
        // @codeCoverageIgnoreEnd

        $newItems = [];

        while ($instances < $maxInstances) {
            $nextDate = clone $currentDate;

            match ($unit) {
                'd' => $nextDate->modify("+{$amount} days"),
                'w' => $nextDate->modify("+{$amount} weeks"),
                'm' => $nextDate->modify("+{$amount} months"),
                'y' => $nextDate->modify("+{$amount} years"),
            };

            if ($endDate && $nextDate > $endDate) {
                break;
            }

            $formattedDate = $nextDate->format('Y-m-d');

            $newDescription = preg_replace(
                '/-> \d{4}[-\/](?:\d{2}[-\/]\d{2}|[Ww]\d{2}|[Qq]\d|\d{2})?|\d{4}/',
                "-> {$formattedDate}",
                $taskRef->task->description,
            );

            // @codeCoverageIgnoreStart
            if (!str_contains($newDescription, '->')) {
                $newDescription = "{$newDescription} -> {$formattedDate}";
            }
            // @codeCoverageIgnoreEnd

            $newItems[] = new Task(
                status: $taskRef->task->status,
                priority: $taskRef->task->priority,
                description: $newDescription,
                tags: $taskRef->task->tags,
                dueDate: new DueDate($formattedDate, $nextDate),
                lineNumber: 0,
                continuationLines: $taskRef->task->continuationLines,
            );

            $currentDate = $nextDate;
            ++$instances;
        }

        $targetFile = $target ? realpath($target) ?: $target : $taskRef->file->path;
        $targetContent = '';

        if (file_exists($targetFile)) {
            $targetContent = file_get_contents($targetFile);
        }

        $targetXitFile = XitParser::parseFile($targetContent, $targetFile);

        if (empty($targetXitFile->groups)) {
            $targetXitFile->groups[] = new TaskGroup(null, [], 1);
        }

        $lastGroup = $targetXitFile->groups[count($targetXitFile->groups) - 1];
        $lastGroup->tasks = array_merge($lastGroup->tasks, $newItems);

        $newContent = XitParser::serializeFile($targetXitFile);
        file_put_contents($targetFile, $newContent);

        $this->info('Created '.count($newItems)." recurring instance(s) in {$targetFile}");

        return self::SUCCESS;
    }
}
