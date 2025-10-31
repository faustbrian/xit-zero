<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\TestCase;



uses(TestCase::class)->in('Feature');

function createTestFile(string $filename, string $content): string
{
    $path = sys_get_temp_dir().'/'.$filename;
    file_put_contents($path, $content);

    return $path;
}

function cleanupTestFile(string $path): void
{
    if (file_exists($path)) {
        unlink($path);
    }
}
