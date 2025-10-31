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
use function mb_trim;
use function preg_match;
use function preg_replace;
use function str_replace;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class UntagCommand extends Command
{
    protected $signature = 'untag
                           {id : Task ID to untag}
                           {tag : Tag name to remove}
                           {--f|file= : Specific .xit file}';

    protected $description = 'Remove tag from task';

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

        /** @codeCoverageIgnoreEnd */
        $task = $group->tasks[$taskRef->taskIndex] ?? null;

        // @codeCoverageIgnoreStart
        if (!$task) {
            $this->error("Item not found for task {$taskId}");

            return self::FAILURE;
        }

        /** @codeCoverageIgnoreEnd */
        $normalizedTag = mb_strtolower(str_replace('#', '', $tagName));
        $tagPattern = "/\\s*#{$normalizedTag}(?:\\b|=)\\S*/i";

        if (!preg_match($tagPattern, $task->description)) {
            $this->info("Task {$taskId} does not have tag #{$normalizedTag}");

            return self::SUCCESS;
        }

        $task->description = mb_trim(preg_replace($tagPattern, '', $task->description));

        $newContent = XitParser::serializeFile($xitFile);
        file_put_contents($taskRef->file->path, $newContent);

        $this->info("Removed tag #{$normalizedTag} from task {$taskId}");

        return self::SUCCESS;
    }
}
