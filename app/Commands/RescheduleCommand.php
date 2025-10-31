<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Commands;

use App\Parser\DueDate;
use App\Parser\XitParser;
use App\Services\DateParser;
use App\Services\FileDiscovery;
use App\Services\TaskIndex;
use Exception;
use LaravelZero\Framework\Commands\Command;

use function array_key_exists;
use function array_map;
use function count;
use function file_get_contents;
use function file_put_contents;
use function preg_match;
use function preg_replace;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class RescheduleCommand extends Command
{
    protected $signature = 'reschedule
                           {ids* : Task IDs to reschedule}
                           {--date= : New due date}
                           {--f|file= : Specific .xit file}';

    protected $description = 'Update task due dates';

    public function handle(): int
    {
        $ids = array_map('intval', $this->argument('ids'));
        $dateStr = $this->option('date');

        // @codeCoverageIgnoreStart
        if (empty($ids)) {
            $this->error('No task IDs provided');

            return self::FAILURE;
        }

        // @codeCoverageIgnoreEnd

        if (!$dateStr) {
            $this->error('No date provided (use --date)');

            return self::FAILURE;
        }

        try {
            $date = DateParser::parse($dateStr);
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $formattedDate = $date->format('Y-m-d');

        $files = FileDiscovery::findXitFiles($this->option('file'));
        $index = new TaskIndex($files);
        $tasks = $index->getTasksByIndices($ids);

        if (empty($tasks)) {
            $this->error('No valid task IDs found');

            return self::FAILURE;
        }

        $fileMap = [];

        foreach ($ids as $i => $id) {
            if (!array_key_exists($i, $tasks)) {
                continue;
            }

            $taskRef = $tasks[$i];
            $filePath = $taskRef->file->path;

            if (!array_key_exists($filePath, $fileMap)) {
                $fileMap[$filePath] = [];
            }

            $fileMap[$filePath][] = $id;
        }

        foreach ($fileMap as $filePath => $taskIds) {
            $content = file_get_contents($filePath);
            $xitFile = XitParser::parseFile($content, $filePath);

            foreach ($taskIds as $taskId) {
                $taskRef = $index->getTaskByIndex($taskId);

                // @codeCoverageIgnoreStart
                if (!$taskRef || $taskRef->file->path !== $filePath) {
                    continue;
                }

                /** @codeCoverageIgnoreEnd */
                $group = $xitFile->groups[$taskRef->groupIndex] ?? null;

                // @codeCoverageIgnoreStart
                if (!$group) {
                    continue;
                }

                /** @codeCoverageIgnoreEnd */
                $task = $group->tasks[$taskRef->taskIndex] ?? null;

                // @codeCoverageIgnoreStart
                if (!$task) {
                    continue;
                }

                /** @codeCoverageIgnoreEnd */
                $dueDatePattern = '/-> \d{4}[-\/](?:\d{2}[-\/]\d{2}|[Ww]\d{2}|[Qq]\d|\d{2})?|\d{4}/';

                if (preg_match($dueDatePattern, $task->description)) {
                    $task->description = preg_replace(
                        $dueDatePattern,
                        "-> {$formattedDate}",
                        $task->description,
                    );
                } else {
                    $task->description = "{$task->description} -> {$formattedDate}";
                }

                $task->dueDate = new DueDate($formattedDate, $date);
            }

            $newContent = XitParser::serializeFile($xitFile);
            file_put_contents($filePath, $newContent);
        }

        $this->info('Rescheduled '.count($tasks)." task(s) to {$formattedDate}");

        return self::SUCCESS;
    }
}
