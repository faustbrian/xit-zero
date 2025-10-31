<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\Services\DateParser;

it('parses today', function (): void {
    $date = DateParser::parse('today');
    expect($date->format('Y-m-d'))->toBe(date('Y-m-d'));
});

it('parses tomorrow', function (): void {
    $date = DateParser::parse('tomorrow');
    $expected = date('Y-m-d', strtotime('+1 day'));
    expect($date->format('Y-m-d'))->toBe($expected);
});

it('parses plus days offset', function (): void {
    $date = DateParser::parse('+1d');
    $expected = date('Y-m-d', strtotime('+1 day'));
    expect($date->format('Y-m-d'))->toBe($expected);
});

it('parses plus weeks offset', function (): void {
    $date = DateParser::parse('+2w');
    $expected = date('Y-m-d', strtotime('+2 weeks'));
    expect($date->format('Y-m-d'))->toBe($expected);
});

it('parses plus months offset', function (): void {
    $date = DateParser::parse('+1m');
    $expected = date('Y-m-d', strtotime('+1 month'));
    expect($date->format('Y-m-d'))->toBe($expected);
});

it('parses plus years offset', function (): void {
    $date = DateParser::parse('+1y');
    $expected = date('Y-m-d', strtotime('+1 year'));
    expect($date->format('Y-m-d'))->toBe($expected);
});

it('parses minus offset', function (): void {
    $date = DateParser::parse('-1w');
    $expected = date('Y-m-d', strtotime('-1 week'));
    expect($date->format('Y-m-d'))->toBe($expected);
});

it('parses absolute date', function (): void {
    $date = DateParser::parse('2025-12-31');
    expect($date->format('Y-m-d'))->toBe('2025-12-31');
});

it('parses year-month date', function (): void {
    $date = DateParser::parse('2025-12');
    expect($date->format('Y-m'))->toBe('2025-12');
});

it('parses year-only date', function (): void {
    $date = DateParser::parse('2025');
    expect($date->format('Y'))->toBe('2025');
    expect($date->format('m-d'))->toBe('12-31');
});

it('throws exception for invalid format', function (): void {
    DateParser::parse('invalid');
})->throws(InvalidArgumentException::class);
