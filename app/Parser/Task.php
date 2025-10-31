<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Parser;

final class Task
{
    public function __construct(
        public string $status,
        public int $priority,
        public string $description,
        /** @var array<Tag> */
        public array $tags,
        public ?DueDate $dueDate,
        public readonly int $lineNumber,
        /** @var array<string> */
        public array $continuationLines = [],
    ) {}
}
