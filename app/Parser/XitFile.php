<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Parser;

final class XitFile
{
    public function __construct(
        public readonly string $path,
        /** @var array<TaskGroup> */
        public array $groups,
        public readonly string $raw,
    ) {}
}
