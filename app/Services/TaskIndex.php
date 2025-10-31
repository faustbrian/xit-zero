<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services;

use App\Parser\Task;
use App\Parser\XitFile;
use App\Parser\XitParser;

use function file_get_contents;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class TaskIndex
{
    /** @var array<XitFile> */
    private array $files = [];

    /** @var array<TaskReference> */
    private array $references = [];

    public function __construct(array $filePaths)
    {
        foreach ($filePaths as $path) {
            $content = file_get_contents($path);

            if ($content === false) {
                continue;
            }

            $xitFile = XitParser::parseFile($content, $path);
            $this->files[] = $xitFile;

            foreach ($xitFile->groups as $groupIndex => $group) {
                foreach ($group->tasks as $taskIndex => $task) {
                    $this->references[] = new TaskReference(
                        file: $xitFile,
                        groupIndex: $groupIndex,
                        taskIndex: $taskIndex,
                        task: $task,
                    );
                }
            }
        }
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getReferences(): array
    {
        return $this->references;
    }

    public function getTaskByIndex(int $index): ?TaskReference
    {
        return $this->references[$index - 1] ?? null;
    }

    public function getTasksByIndices(array $indices): array
    {
        $tasks = [];

        foreach ($indices as $index) {
            $task = $this->getTaskByIndex($index);

            if ($task) {
                $tasks[] = $task;
            }
        }

        return $tasks;
    }

    public function getAllTasks(): array
    {
        return $this->references;
    }
}

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class TaskReference
{
    public function __construct(
        public readonly XitFile $file,
        public readonly int $groupIndex,
        public readonly int $taskIndex,
        public readonly Task $task,
    ) {}
}
