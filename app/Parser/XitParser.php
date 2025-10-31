<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Parser;

use DateTime;
use Exception;

use const PREG_SET_ORDER;

use function count;
use function end;
use function mb_ltrim;
use function mb_stripos;
use function mb_strlen;
use function mb_strtolower;
use function mb_substr;
use function mb_substr_count;
use function mb_trim;
use function preg_match;
use function preg_match_all;
use function preg_split;
use function str_repeat;
use function str_replace;
use function str_starts_with;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class XitParser
{
    private const string CHECKBOX_PATTERN = '/^\[([ x@~?])\]/';

    private const string PRIORITY_PATTERN = '/^([!.]+)/';

    private const string DUE_DATE_PATTERN = '/-> (\d{4}[-\/](?:\d{2}[-\/]\d{2}|[Ww]\d{2}|[Qq]\d|\d{2})?|\d{4})/';

    private const string TAG_PATTERN = '/#([\w-]+)(?:=(?:"([^"\n]*)"|\'([^\'\n]*)\'|([\w-]+)))?/';

    private const string CONTINUATION_LINE = '/^ {4}(.+)/';

    public static function parseFile(string $content, string $filePath): XitFile
    {
        $lines = preg_split('/\r?\n/', $content);
        $groups = [];
        $currentGroup = null;
        $currentTask = null;
        $currentContinuationLines = [];

        foreach ($lines as $i => $line) {
            $lineNumber = $i + 1;

            if (mb_trim($line) === '') {
                $currentGroup = null;
                $currentTask = null;
                $currentContinuationLines = [];

                continue;
            }

            if (preg_match(self::CONTINUATION_LINE, $line, $continuationMatch) && $currentTask) {
                $currentContinuationLines[] = $continuationMatch[1];

                continue;
            }

            $task = self::parseTask($line, $lineNumber);

            if ($task) {
                if (!$currentGroup) {
                    $currentGroup = new TaskGroup(null, [], $lineNumber);
                    $groups[] = $currentGroup;
                }

                $currentGroup->tasks[] = $task;

                // Apply accumulated continuation lines to previous task
                if ($currentTask && !empty($currentContinuationLines)) {
                    $currentTask->continuationLines = $currentContinuationLines;
                }

                $currentTask = $task;
                $currentContinuationLines = [];
            } elseif (!str_starts_with($line, '[') && mb_trim($line) !== '') {
                if ($currentGroup && count($currentGroup->tasks) === 0) {
                    // @codeCoverageIgnoreStart
                    $currentGroup->title = $line; // Unreachable: TaskGroup::$title is readonly
                    // @codeCoverageIgnoreEnd
                } else {
                    $currentGroup = new TaskGroup($line, [], $lineNumber);
                    $groups[] = $currentGroup;
                }

                $currentTask = null;
            }
        }

        // Apply any remaining continuation lines to the last task
        if ($currentTask && !empty($currentContinuationLines)) {
            $currentTask->continuationLines = $currentContinuationLines;
        }

        return new XitFile($filePath, $groups, $content);
    }

    public static function serializeFile(XitFile $file): string
    {
        $output = '';
        $lastLineNumber = 0;

        foreach ($file->groups as $group) {
            $blankLinesBefore = $group->startLine - $lastLineNumber - 1;

            if ($blankLinesBefore > 0 && $output !== '') {
                $output .= str_repeat("\n", $blankLinesBefore);
            }

            if ($group->title) {
                $output .= $group->title."\n";
            }

            foreach ($group->tasks as $task) {
                $output .= self::serializeTask($task)."\n";
            }

            $lastTask = end($group->tasks);

            if ($lastTask) {
                $lastLineNumber = $lastTask->lineNumber;
            } elseif ($group->title) {
                $lastLineNumber = $group->startLine;
            }
        }

        return $output;
    }

    private static function parseCheckbox(string $char): string
    {
        return match ($char) {
            ' ' => 'open',
            'x' => 'checked',
            '@' => 'ongoing',
            '~' => 'obsolete',
            '?' => 'inquestion',
            default => throw new Exception("Invalid checkbox character: {$char}"),
        };
    }

    private static function parsePriority(string $priorityStr): int
    {
        return mb_substr_count($priorityStr, '!');
    }

    private static function parseTags(string $description): array
    {
        $tags = [];

        if (preg_match_all(self::TAG_PATTERN, $description, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = mb_strtolower($match[1]);
                $value = (!empty($match[2])) ? $match[2] : ((!empty($match[3])) ? $match[3] : ((!empty($match[4])) ? $match[4] : null));

                if ($value !== null && $value !== '') {
                    $tags[] = new Tag($name, $value);
                } else {
                    $tags[] = new Tag($name);
                }
            }
        }

        return $tags;
    }

    private static function parseDueDate(string $description): ?DueDate
    {
        if (!preg_match(self::DUE_DATE_PATTERN, $description, $match)) {
            return null;
        }

        $raw = $match[1];

        if (mb_stripos($raw, 'W') !== false) {
            $parts = preg_split('/[Ww]/', $raw);
            $date = self::getDateFromWeek((int) $parts[0], (int) $parts[1]);
        } elseif (mb_stripos($raw, 'Q') !== false) {
            $parts = preg_split('/[Qq]/', $raw);
            $date = self::getDateFromQuarter((int) $parts[0], (int) $parts[1]);
        } elseif (mb_strlen($raw) === 4) {
            $date = new DateTime($raw.'-12-31');
        } elseif (mb_strlen($raw) === 7) {
            $parts = preg_split('/[-\/]/', $raw);
            $year = (int) $parts[0];
            $month = (int) $parts[1];
            $date = new DateTime("{$year}-{$month}-01");
            $date->modify('last day of this month');
        } else {
            $normalized = str_replace('/', '-', $raw);
            $date = new DateTime($normalized);
        }

        return new DueDate($raw, $date);
    }

    private static function getDateFromWeek(int $year, int $week): DateTime
    {
        $jan4 = new DateTime("{$year}-01-04");
        $dayOfWeek = (int) $jan4->format('N');

        $firstMonday = clone $jan4;
        $firstMonday->modify('-'.($dayOfWeek - 1).' days');

        $targetDate = clone $firstMonday;
        $targetDate->modify('+'.(($week - 1) * 7 + 6).' days');

        return $targetDate;
    }

    private static function getDateFromQuarter(int $year, int $quarter): DateTime
    {
        $month = $quarter * 3;
        $date = new DateTime("{$year}-{$month}-01");
        $date->modify('last day of this month');

        return $date;
    }

    private static function parseTask(string $line, int $lineNumber): ?Task
    {
        if (!preg_match(self::CHECKBOX_PATTERN, $line, $checkboxMatch)) {
            return null;
        }

        $status = self::parseCheckbox($checkboxMatch[1]);
        $rest = mb_ltrim(mb_substr($line, mb_strlen($checkboxMatch[0])));

        $priority = 0;

        if (preg_match(self::PRIORITY_PATTERN, $rest, $priorityMatch)) {
            $priority = self::parsePriority($priorityMatch[1]);
            $rest = mb_ltrim(mb_substr($rest, mb_strlen($priorityMatch[0])));
        }

        $description = $rest;
        $tags = self::parseTags($description);
        $dueDate = self::parseDueDate($description);

        return new Task(
            status: $status,
            priority: $priority,
            description: $description,
            tags: $tags,
            dueDate: $dueDate,
            lineNumber: $lineNumber,
        );
    }

    private static function serializeTask(Task $task): string
    {
        $statusChar = match ($task->status) {
            'open' => ' ',
            'checked' => 'x',
            'ongoing' => '@',
            'obsolete' => '~',
            'inquestion' => '?',
        };

        $line = "[{$statusChar}]";

        if ($task->priority > 0) {
            $line .= ' '.str_repeat('!', $task->priority);
        }

        $line .= ' '.$task->description;

        foreach ($task->continuationLines as $continuation) {
            $line .= "\n    ".$continuation;
        }

        return $line;
    }
}
