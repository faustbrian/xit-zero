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

use function explode;
use function implode;
use function mb_strlen;
use function mb_substr;
use function preg_replace;
use function sprintf;
use function str_repeat;
use function Termwind\render;
use function wordwrap;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class RenderCommand extends Command
{
    private const array STATUS_SYMBOLS = [
        'open' => ' ',
        'checked' => 'x',
        'ongoing' => '@',
        'obsolete' => '~',
        'inquestion' => '?',
    ];

    protected $signature = 'render
                           {--f|file= : Specific .xit file to render}
                           {--status= : Filter by status (open, checked, ongoing, obsolete, inquestion)}
                           {--show-id : Show task IDs}
                           {--subdir : Include subdirectories}';

    protected $description = 'Render tasks from .xit files with official spec styling';

    public function handle(): int
    {
        $files = FileDiscovery::findXitFiles(
            $this->option('file'),
            $this->option('subdir'),
        );

        // @codeCoverageIgnoreStart
        if (empty($files)) {
            render('<div class="text-gray-400">No .xit files found</div>');

            return self::SUCCESS;
        }

        /** @codeCoverageIgnoreEnd */
        $index = new TaskIndex($files);
        $tasks = $index->getAllTasks();

        if (empty($tasks)) {
            render('<div class="text-gray-400">No tasks found</div>');

            return self::SUCCESS;
        }

        $statusFilter = $this->option('status');
        $showId = $this->option('show-id');
        $currentFile = '';
        $currentGroup = null;
        $lastLineNumber = 0;

        foreach ($tasks as $i => $taskRef) {
            if ($statusFilter && $taskRef->task->status !== $statusFilter) {
                continue;
            }

            if ($taskRef->file->path !== $currentFile) {
                // @codeCoverageIgnoreStart
                if ($currentFile !== '') {
                    $this->newLine();
                }

                /** @codeCoverageIgnoreEnd */
                $currentFile = $taskRef->file->path;
                render('<div class="font-bold text-white">'.$currentFile.'</div>');
                $currentGroup = null;
                $lastLineNumber = 0;
            }

            $group = $taskRef->file->groups[$taskRef->groupIndex];

            if ($currentGroup !== $taskRef->groupIndex) {
                $currentGroup = $taskRef->groupIndex;

                // Check if there's a gap (blank line) before this group
                $hasGap = $lastLineNumber > 0 && $group->startLine - $lastLineNumber > 1;

                if ($hasGap || $group->title) {
                    $this->newLine();
                }

                if ($group->title) {
                    render('<div class="font-bold text-white underline">'.$group->title.'</div>');
                }
            }

            $id = $i + 1;
            $idHtml = $showId ? '<span class="text-gray-400">['.$id.']</span> ' : '';
            $statusSymbol = self::STATUS_SYMBOLS[$taskRef->task->status];

            [$statusColor, $textColor] = $this->getColors($taskRef->task->status);

            $priority = str_repeat('!', $taskRef->task->priority);
            $priorityHtml = $priority ? '<span class="text-red-400 font-bold">'.$priority.'</span> ' : '';

            $description = $taskRef->task->description;

            // @codeCoverageIgnoreStart
            if (!empty($taskRef->task->continuationLines)) {
                $description .= ' '.implode(' ', $taskRef->task->continuationLines);
            }

            // Highlight dates in description
            $description = preg_replace(
                '/(->)\s+(\d{4}[-\/](?:\d{2}[-\/]\d{2}|[Ww]\d{2}|[Qq]\d|\d{2})?|\d{4})/',
                '<span class="text-yellow-200">$1 $2</span>',
                $description,
            );

            // Highlight tags in description
            $description = preg_replace(
                '/#([\w-]+)(?:=(?:"([^"\n]*)"|\'([^\'\n]*)\'|([\w-]+)))?/',
                '<span class="text-cyan-400">$0</span>',
                $description,
            );

            // Calculate prefix length for wrapping
            $prefixLength = mb_strlen($idHtml) + 4 + mb_strlen($priority) + ($priority ? 1 : 0) + 1;
            $wrappedDesc = $this->wrapText($description, 80, str_repeat(' ', $prefixLength));

            // @codeCoverageIgnoreEnd

            render(sprintf(
                '<div>%s<span class="%s">[%s]</span> %s<span class="%s">%s</span></div>',
                $idHtml,
                $statusColor,
                $statusSymbol,
                $priorityHtml,
                $textColor,
                $wrappedDesc,
            ));

            $lastLineNumber = $taskRef->task->lineNumber;
        }

        return self::SUCCESS;
    }

    private function getColors(string $status): array
    {
        // @codeCoverageIgnoreStart
        return match ($status) {
            'open' => ['text-blue-400', 'text-white'],
            'checked' => ['text-green-400', 'text-gray-500'],
            'ongoing' => ['text-pink-400', 'text-white'],
            'obsolete' => ['text-gray-500', 'text-gray-500'],
            'inquestion' => ['text-yellow-300', 'text-white'],
            default => ['text-blue-400', 'text-white'],
        };
        // @codeCoverageIgnoreEnd
    }

    private function wrapText(string $text, int $width = 80, string $prefix = ''): string
    {
        $wrapped = wordwrap($text, $width - mb_strlen($prefix), "\n", false);
        $lines = explode("\n", $wrapped);

        return implode("\n".$prefix, $lines);
    }
}
