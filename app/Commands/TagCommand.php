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

use function file_get_contents;
use function file_put_contents;
use function mb_strtolower;
use function str_contains;
use function str_replace;

final class TagCommand extends Command
{
    protected $signature = 'tag
                           {id : Task ID to tag}
                           {tag : Tag name to add}
                           {--f|file= : Specific .xit file}';

    protected $description = 'Add tag to task';

    public function handle(): int
    {
        $taskId = (int) $this->argument('id');
        $tagName = $this->argument('tag');

        $files = FileDiscovery::findXitFiles($this->option('file'));
        $index = new TaskIndex($files);
        $taskRef = $index->getTaskByIndex($taskId);

        if (!$taskRef) {
            $this->error("Task {$taskId} not found");

            return self::FAILURE;
        }

        $content = file_get_contents($taskRef->file->path);
        $xitFile = XitParser::parseFile($content, $taskRef->file->path);

        $group = $xitFile->groups[$taskRef->groupIndex] ?? null;

        // @codeCoverageIgnoreStart
        if (!$group) {
            $this->error("Group not found for task {$taskId}");

            return self::FAILURE;
        }
        // @codeCoverageIgnoreEnd

        $task = $group->tasks[$taskRef->taskIndex] ?? null;

        // @codeCoverageIgnoreStart
        if (!$task) {
            $this->error("Item not found for task {$taskId}");

            return self::FAILURE;
        }
        // @codeCoverageIgnoreEnd

        $normalizedTag = mb_strtolower(str_replace('#', '', $tagName));

        foreach ($task->tags as $tag) {
            if ($tag->name === $normalizedTag) {
                $this->info("Task {$taskId} already has tag #{$normalizedTag}");

                return self::SUCCESS;
            }
        }

        // @codeCoverageIgnoreStart
        if (str_contains($task->description, "#{$normalizedTag}")) {
            $this->info("Task {$taskId} already has tag #{$normalizedTag} in description");

            return self::SUCCESS;
        }
        // @codeCoverageIgnoreEnd

        $task->description = "{$task->description} #{$normalizedTag}";

        $newContent = XitParser::serializeFile($xitFile);
        file_put_contents($taskRef->file->path, $newContent);

        $this->info("Added tag #{$normalizedTag} to task {$taskId}");

        return self::SUCCESS;
    }
}
