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
use function count;
use function file_get_contents;
use function file_put_contents;

final class MarkCommand extends Command
{
    protected $signature = 'mark
                           {ids* : Task IDs to mark}
                           {--done : Mark as done/checked}
                           {--open : Mark as open}
                           {--ongoing : Mark as ongoing}
                           {--obsolete : Mark as obsolete}
                           {--inquestion : Mark as in question}
                           {--f|file= : Specific .xit file}';

    protected $description = 'Change task status';

    public function handle(): int
    {
        $ids = array_map('intval', $this->argument('ids'));

        // @codeCoverageIgnoreStart
        if (empty($ids)) {
            $this->error('No task IDs provided');

            return self::FAILURE;
        }
        // @codeCoverageIgnoreEnd

        $statusFlags = [
            'done' => $this->option('done'),
            'open' => $this->option('open'),
            'ongoing' => $this->option('ongoing'),
            'obsolete' => $this->option('obsolete'),
            'inquestion' => $this->option('inquestion'),
        ];

        $flagCount = count(array_filter($statusFlags));

        if ($flagCount === 0) {
            $this->error('No status flag provided (--done, --open, --ongoing, --obsolete, --inquestion)');

            return self::FAILURE;
        }

        if ($flagCount > 1) {
            $this->error('Only one status flag can be specified');

            return self::FAILURE;
        }

        $newStatus = match (true) {
            $this->option('done') => 'checked',
            $this->option('open') => 'open',
            $this->option('ongoing') => 'ongoing',
            $this->option('obsolete') => 'obsolete',
            $this->option('inquestion') => 'inquestion',
        };

        $files = FileDiscovery::findXitFiles($this->option('file'));
        $index = new TaskIndex($files);
        $tasks = $index->getTasksByIndices($ids);

        // @codeCoverageIgnoreStart
        if (empty($tasks)) {
            $this->error('No valid task IDs found');

            return self::FAILURE;
        }
        // @codeCoverageIgnoreEnd

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

        foreach ($fileMap as $filePath => $taskIds) {
            $content = file_get_contents($filePath);
            $xitFile = XitParser::parseFile($content, $filePath);

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

                if ($task) {
                    $task->status = $newStatus;
                }
            }

            $newContent = XitParser::serializeFile($xitFile);
            file_put_contents($filePath, $newContent);
        }

        $statusName = $newStatus === 'checked' ? 'done' : $newStatus;
        $this->info('Marked '.count($tasks)." task(s) as {$statusName}");

        return self::SUCCESS;
    }
}
