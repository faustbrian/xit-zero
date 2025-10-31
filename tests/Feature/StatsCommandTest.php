<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

beforeEach(function (): void {
    $this->testFile = createTestFile('test.xit', "[ ] Task one\n[x] Task two\n[@] Task three");
});

afterEach(function (): void {
    cleanupTestFile($this->testFile);
});

it('shows task statistics', function (): void {
    $this->artisan("stats --file {$this->testFile}")
        ->expectsOutputToContain('Task Statistics')
        ->expectsOutputToContain('Total tasks:')
        ->assertExitCode(0);
});

it('calculates completion rate', function (): void {
    $this->artisan("stats --file {$this->testFile}")
        ->expectsOutputToContain('Completion rate:')
        ->assertExitCode(0);
});

it('shows 0.0% completion for empty file', function (): void {
    $emptyFile = createTestFile('empty.xit', '');

    $this->artisan("stats --file {$emptyFile}")
        ->expectsOutputToContain('0.0%')
        ->assertExitCode(0);

    cleanupTestFile($emptyFile);
});

it('counts tasks with priorities', function (): void {
    $file = createTestFile('priority.xit', "[ ] ! Task one\n[ ] !! Task two");

    $this->artisan("stats --file {$file}")
        ->expectsOutputToContain('With priority:')
        ->assertExitCode(0);

    cleanupTestFile($file);
});

it('counts tasks with tags', function (): void {
    $file = createTestFile('tags.xit', "[ ] Task #urgent\n[ ] Task #work");

    $this->artisan("stats --file {$file}")
        ->expectsOutputToContain('With tags:')
        ->assertExitCode(0);

    cleanupTestFile($file);
});
