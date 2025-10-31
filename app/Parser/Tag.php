<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Parser;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class Tag
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $value = null,
    ) {}
}
