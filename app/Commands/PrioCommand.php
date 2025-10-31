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

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class PrioCommand extends Command
{
    protected $signature = 'prio
                           {id : Task ID to set priority for}
                           {priority : Priority level (0-3)}
                           {--f|file= : Specific .xit file}';

    protected $description = 'Set task priority';

    public function handle(): int
    {
        $taskId = (int) $this->argument('id');
        $priority = (int) $this->argument('priority');

        if ($priority < 0 || $priority > 3) {
            $this->error('Priority must be between 0 and 3');

            return self::FAILURE;
        }

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

        // @codeCoverageIgnoreEnd

        $task->priority = $priority;

        $newContent = XitParser::serializeFile($xitFile);
        file_put_contents($taskRef->file->path, $newContent);

        if ($priority === 0) {
            $this->info("Removed priority from task {$taskId}");
        } else {
            $this->info("Set task {$taskId} priority to {$priority}");
        }

        return self::SUCCESS;
    }
}
