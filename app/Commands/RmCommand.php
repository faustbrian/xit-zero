<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Commands;

use App\Parser\XitParser;
use App\Services\FileDiscovery;
use App\Services\TaskIndex;
use LaravelZero\Framework\Commands\Command;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_splice;
use function count;
use function file_get_contents;
use function file_put_contents;
use function rsort;

final class RmCommand extends Command
{
    protected $signature = 'rm
                           {ids* : Task IDs to remove}
                           {--force : Skip confirmation prompts}
                           {--f|file= : Specific .xit file}';

    protected $description = 'Remove tasks';

    public function handle(): int
    {
        $ids = array_map('intval', $this->argument('ids'));

        // @codeCoverageIgnoreStart
        if (empty($ids)) {
            $this->error('No task IDs provided');

            return self::FAILURE;
        }
        // @codeCoverageIgnoreEnd

        $files = FileDiscovery::findXitFiles($this->option('file'));
        $index = new TaskIndex($files);
        $tasks = $index->getTasksByIndices($ids);

        if (empty($tasks)) {
            $this->error('No valid task IDs found');

            return self::FAILURE;
        }

        $fileMap = [];

        foreach ($ids as $i => $id) {
            // @codeCoverageIgnoreStart
            if (!array_key_exists($i, $tasks)) {
                continue;
            }
            // @codeCoverageIgnoreEnd

            $taskRef = $tasks[$i];
            $filePath = $taskRef->file->path;

            if (!array_key_exists($filePath, $fileMap)) {
                $fileMap[$filePath] = [];
            }

            $fileMap[$filePath][] = $id;
        }

        $removedCount = 0;

        foreach ($fileMap as $filePath => $taskIds) {
            $content = file_get_contents($filePath);
            $xitFile = XitParser::parseFile($content, $filePath);

            rsort($taskIds);

            foreach ($taskIds as $taskId) {
                $taskRef = $index->getTaskByIndex($taskId);

                // @codeCoverageIgnoreStart
                if (!$taskRef || $taskRef->file->path !== $filePath) {
                    continue;
                }
                // @codeCoverageIgnoreEnd

                $group = $xitFile->groups[$taskRef->groupIndex] ?? null;

                // @codeCoverageIgnoreStart
                if (!$group) {
                    continue;
                }
                // @codeCoverageIgnoreEnd

                $task = $group->tasks[$taskRef->taskIndex] ?? null;

                // @codeCoverageIgnoreStart
                if (!$task) {
                    continue;
                }
                // @codeCoverageIgnoreEnd

                // @codeCoverageIgnoreStart
                if (!$this->option('force')) {
                    if (!$this->confirm("Remove task [{$taskId}] \"{$task->description}\"?", false)) {
                        continue;
                    }
                }
                // @codeCoverageIgnoreEnd

                array_splice($group->tasks, $taskRef->taskIndex, 1);
                ++$removedCount;
            }

            $xitFile->groups = array_filter($xitFile->groups, function ($group) {
                return count($group->tasks) > 0 || $group->title !== null;
            });

            $newContent = XitParser::serializeFile($xitFile);
            file_put_contents($filePath, $newContent);
        }

        $this->info("Removed {$removedCount} task(s)");

        return self::SUCCESS;
    }
}
