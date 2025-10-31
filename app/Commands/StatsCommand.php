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

use function count;
use function number_format;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class StatsCommand extends Command
{
    protected $signature = 'stats
                           {--f|file= : Specific .xit file to analyze}
                           {--subdir : Include subdirectories}';

    protected $description = 'Show task statistics';

    public function handle(): int
    {
        $files = FileDiscovery::findXitFiles(
            $this->option('file'),
            $this->option('subdir'),
        );

        // @codeCoverageIgnoreStart
        if (empty($files)) {
            $this->info('No .xit files found');

            return self::SUCCESS;
        }

        /** @codeCoverageIgnoreEnd */
        $index = new TaskIndex($files);
        $tasks = $index->getAllTasks();

        $stats = [
            'total' => count($tasks),
            'open' => 0,
            'checked' => 0,
            'ongoing' => 0,
            'obsolete' => 0,
            'inquestion' => 0,
            'withPriority' => 0,
            'withTags' => 0,
            'withDueDate' => 0,
        ];

        foreach ($tasks as $taskRef) {
            ++$stats[$taskRef->task->status];

            if ($taskRef->task->priority > 0) {
                ++$stats['withPriority'];
            }

            if (!empty($taskRef->task->tags)) {
                ++$stats['withTags'];
            }

            // @codeCoverageIgnoreStart
            if ($taskRef->task->dueDate !== null) {
                ++$stats['withDueDate'];
            }
            // @codeCoverageIgnoreEnd
        }

        $completionRate = $stats['total'] > 0
            ? number_format(($stats['checked'] / $stats['total']) * 100, 1)
            : '0.0';

        $this->line('<fg=white;options=bold>Task Statistics</>');
        $this->newLine();
        $this->line("Total tasks:      {$stats['total']}");
        $this->line("<fg=gray>Open:             {$stats['open']}</>");
        $this->line("<fg=green>Checked:          {$stats['checked']}</>");
        $this->line("<fg=yellow>Ongoing:          {$stats['ongoing']}</>");
        $this->line("<fg=gray>Obsolete:         {$stats['obsolete']}</>");
        $this->line("In question:      {$stats['inquestion']}");
        $this->newLine();
        $this->line("With priority:    {$stats['withPriority']}");
        $this->line("With tags:        {$stats['withTags']}");
        $this->line("With due date:    {$stats['withDueDate']}");
        $this->newLine();
        $this->line("<fg=white;options=bold>Completion rate:  {$completionRate}%</>");

        return self::SUCCESS;
    }
}
