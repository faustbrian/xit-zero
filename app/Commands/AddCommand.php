<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Commands;

use App\Services\FileDiscovery;
use LaravelZero\Framework\Commands\Command;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function getcwd;
use function realpath;
use function str_ends_with;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class AddCommand extends Command
{
    protected $signature = 'add
                           {description : Task description}
                           {--f|file= : Target .xit file}';

    protected $description = 'Add a new task';

    public function handle(): int
    {
        $description = $this->argument('description');
        $targetFile = $this->option('file');

        if ($targetFile) {
            $targetFile = realpath($targetFile) ?: $targetFile;
        } else {
            /** @codeCoverageIgnoreStart */
            $files = FileDiscovery::findXitFiles();
            $targetFile = !empty($files) ? $files[0] : getcwd().'/tasks.xit';
            // @codeCoverageIgnoreEnd
        }

        $content = '';

        if (file_exists($targetFile)) {
            $content = file_get_contents($targetFile);
        }

        // @codeCoverageIgnoreStart
        if ($content && !str_ends_with($content, "\n")) {
            $content .= "\n";
        }

        // @codeCoverageIgnoreEnd

        if ($content && !str_ends_with($content, "\n\n")) {
            $content .= "\n";
        }

        $content .= "[ ] {$description}\n";

        file_put_contents($targetFile, $content);

        $this->info("Task added to {$targetFile}");

        return self::SUCCESS;
    }
}
