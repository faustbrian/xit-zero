<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Parser;

final class TaskGroup
{
    public function __construct(
        public readonly ?string $title,
        /** @var array<Task> */
        public array $tasks,
        public readonly int $startLine,
    ) {}
}
