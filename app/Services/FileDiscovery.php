<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function file_exists;
use function getcwd;
use function is_dir;
use function sort;
use function str_ends_with;

final class FileDiscovery
{
    public static function findXitFiles(?string $path = null, bool $includeSubdirs = false): array
    {
        $searchPath = $path ?? getcwd();

        if (!is_dir($searchPath)) {
            if (file_exists($searchPath) && str_ends_with($searchPath, '.xit')) {
                return [$searchPath];
            }

            return [];
        }

        $files = [];

        if ($includeSubdirs) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($searchPath, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST,
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.xit')) {
                    $files[] = $file->getPathname();
                }
            }
        } else {
            $iterator = new FilesystemIterator($searchPath, FilesystemIterator::SKIP_DOTS);

            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.xit')) {
                    $files[] = $file->getPathname();
                }
            }
        }

        sort($files);

        return $files;
    }
}
