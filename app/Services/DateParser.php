<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services;

use DateTime;
use InvalidArgumentException;

use function mb_strtolower;
use function mb_trim;
use function preg_match;
use function strcasecmp;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class DateParser
{
    public static function parse(string $input): DateTime
    {
        $input = mb_trim($input);

        // Relative dates
        if (strcasecmp($input, 'today') === 0) {
            return new DateTime('today');
        }

        if (strcasecmp($input, 'tomorrow') === 0) {
            return new DateTime('tomorrow');
        }

        // Offset patterns: +1d, +1w, +2m, +1y, -1w
        if (preg_match('/^([+-])(\d+)([dwmy])$/i', $input, $match)) {
            $direction = $match[1];
            $amount = (int) $match[2];
            $unit = mb_strtolower($match[3]);

            $modifier = match ($unit) {
                'd' => 'days',
                'w' => 'weeks',
                'm' => 'months',
                'y' => 'years',
            };

            $date = new DateTime();
            $date->modify("{$direction}{$amount} {$modifier}");

            return $date;
        }

        // Absolute dates
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) {
            return new DateTime($input);
        }

        if (preg_match('/^\d{4}-\d{2}$/', $input)) {
            $date = new DateTime($input.'-01');
            $date->modify('last day of this month');

            return $date;
        }

        if (preg_match('/^\d{4}$/', $input)) {
            return new DateTime($input.'-12-31');
        }

        throw new InvalidArgumentException("Invalid date format: {$input}");
    }
}
