<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

beforeEach(function (): void {
    $this->testFile = createTestFile('test.xit', "[ ] Task one\n[ ] Task two\n[ ] Task three");
});

afterEach(function (): void {
    cleanupTestFile($this->testFile);
});

it('reschedules task with specific date', function (): void {
    $this->artisan("reschedule 1 --date 2025-12-31 --file {$this->testFile}")
        ->expectsOutputToContain('Rescheduled')
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('-> 2025-12-31');
});

it('reschedules task to today', function (): void {
    $today = date('Y-m-d');

    $this->artisan("reschedule 1 --date today --file {$this->testFile}")
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain("-> {$today}");
});

it('reschedules task to tomorrow', function (): void {
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    $this->artisan("reschedule 1 --date tomorrow --file {$this->testFile}")
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain("-> {$tomorrow}");
});

it('reschedules task with relative offset', function (): void {
    $this->artisan("reschedule 1 --date +1w --file {$this->testFile}")
        ->assertExitCode(0);
});

it('reschedules multiple tasks', function (): void {
    $this->artisan("reschedule 1 2 --date 2025-12-31 --file {$this->testFile}")
        ->expectsOutputToContain('2 task(s)')
        ->assertExitCode(0);
});

it('updates existing due date', function (): void {
    $file = createTestFile('dated.xit', '[ ] Task -> 2025-01-01');

    $this->artisan("reschedule 1 --date 2025-12-31 --file {$file}")
        ->assertExitCode(0);

    $content = file_get_contents($file);
    expect($content)->toContain('-> 2025-12-31');
    expect($content)->not->toContain('2025-01-01');

    cleanupTestFile($file);
});

it('requires date flag', function (): void {
    $this->artisan("reschedule 1 --file {$this->testFile}")
        ->expectsOutputToContain('No date provided')
        ->assertExitCode(1);
});

it('rejects invalid date format', function (): void {
    $this->artisan("reschedule 1 --date invalid-date --file {$this->testFile}")
        ->assertExitCode(1);
});

it('handles no valid task IDs found', function (): void {
    $this->artisan("reschedule 999 --date 2025-12-31 --file {$this->testFile}")
        ->expectsOutputToContain('No valid task IDs found')
        ->assertExitCode(1);
});

it('reschedules only valid tasks when given mix of valid and invalid IDs', function (): void {
    $this->artisan("reschedule 1 999 2 --date 2025-12-31 --file {$this->testFile}")
        ->expectsOutputToContain('2 task(s)')
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('-> 2025-12-31');
});
