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

it('shows all tasks', function (): void {
    $this->artisan("show --file {$this->testFile}")
        ->expectsOutputToContain('Task one')
        ->assertExitCode(0);
});

it('shows tasks with IDs', function (): void {
    $this->artisan("show --file {$this->testFile} --show-id")
        ->assertExitCode(0);
});

it('filters tasks by status', function (): void {
    $this->artisan("show --file {$this->testFile} --status open")
        ->assertExitCode(0);
});

it('handles empty files', function (): void {
    $emptyFile = createTestFile('empty.xit', '');

    $this->artisan("show --file {$emptyFile}")
        ->expectsOutputToContain('No tasks found')
        ->assertExitCode(0);

    cleanupTestFile($emptyFile);
});

it('shows tasks with continuation lines', function (): void {
    $file = createTestFile('continuation.xit', "[ ] Task one\n    line two\n    line three");

    $this->artisan("show --file {$file}")
        ->assertExitCode(0);

    cleanupTestFile($file);
});
