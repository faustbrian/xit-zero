<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Commands;

use App\Parser\TaskGroup;
use App\Parser\XitParser;
use App\Services\FileDiscovery;
use App\Services\TaskIndex;
use LaravelZero\Framework\Commands\Command;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_reverse;
use function array_splice;
use function count;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function realpath;
use function rsort;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class MoveCommand extends Command
{
    protected $signature = 'move
                           {ids* : Task IDs to move}
                           {--target= : Target .xit file}
                           {--f|file= : Source .xit file}';

    protected $description = 'Move tasks between files';

    public function handle(): int
    {
        $ids = array_map('intval', $this->argument('ids'));
        $target = $this->option('target');

        // @codeCoverageIgnoreStart
        if (empty($ids)) {
            $this->error('No task IDs provided');

            return self::FAILURE;
        }

        // @codeCoverageIgnoreEnd

        if (!$target) {
            $this->error('Target file not specified (use --target)');

            return self::FAILURE;
        }

        $targetFile = realpath($target) ?: $target;

        $files = FileDiscovery::findXitFiles($this->option('file'));
        $index = new TaskIndex($files);
        $tasks = $index->getTasksByIndices($ids);

        // @codeCoverageIgnoreStart
        if (empty($tasks)) {
            $this->error('No valid task IDs found');

            return self::FAILURE;
        }

        /** @codeCoverageIgnoreEnd */
        $fileMap = [];

        foreach ($ids as $i => $id) {
            // @codeCoverageIgnoreStart
            if (!array_key_exists($i, $tasks)) {
                continue;
            }

            /** @codeCoverageIgnoreEnd */
            $taskRef = $tasks[$i];
            $filePath = $taskRef->file->path;

            if (!array_key_exists($filePath, $fileMap)) {
                $fileMap[$filePath] = [];
            }

            $fileMap[$filePath][] = $id;
        }

        $movedItems = [];

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

                // @codeCoverageIgnoreEnd

                $movedItems[] = $task;
                array_splice($group->tasks, $taskRef->taskIndex, 1);
            }

            $xitFile->groups = array_filter($xitFile->groups, function ($group) {
                return count($group->tasks) > 0 || $group->title !== null;
            });

            $newContent = XitParser::serializeFile($xitFile);
            file_put_contents($filePath, $newContent);
        }

        $targetContent = '';

        if (file_exists($targetFile)) {
            $targetContent = file_get_contents($targetFile);
        }

        $targetXitFile = XitParser::parseFile($targetContent, $targetFile);

        if (empty($targetXitFile->groups)) {
            $targetXitFile->groups[] = new TaskGroup(null, [], 1);
        }

        $lastGroup = $targetXitFile->groups[count($targetXitFile->groups) - 1];
        $lastGroup->tasks = array_merge($lastGroup->tasks, array_reverse($movedItems));

        $newTargetContent = XitParser::serializeFile($targetXitFile);
        file_put_contents($targetFile, $newTargetContent);

        $this->info('Moved '.count($movedItems)." task(s) to {$targetFile}");

        return self::SUCCESS;
    }
}
