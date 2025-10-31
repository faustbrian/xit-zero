<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Exception\RuntimeException;



beforeEach(function (): void {
    $this->testFile = createTestFile('test.xit', "[ ] Task one\n[ ] Task two\n[ ] Task three");
});

afterEach(function (): void {
    cleanupTestFile($this->testFile);
});

it('removes task with force flag', function (): void {
    $this->artisan("rm 1 --force --file {$this->testFile}")
        ->expectsOutputToContain('Removed')
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->not->toContain('Task one');
    expect($content)->toContain('Task two');
});

it('removes multiple tasks', function (): void {
    $this->artisan("rm 1 2 --force --file {$this->testFile}")
        ->expectsOutputToContain('2 task(s)')
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->not->toContain('Task one');
    expect($content)->not->toContain('Task two');
    expect($content)->toContain('Task three');
});

it('requires task IDs', function (): void {
    $this->expectException(RuntimeException::class);
    $this->artisan("rm --force --file {$this->testFile}");
});

it('handles invalid task ID', function (): void {
    $this->artisan("rm 999 --force --file {$this->testFile}")
        ->expectsOutputToContain('No valid task IDs found')
        ->assertExitCode(1);
});

it('removes only valid tasks when given mix of valid and invalid IDs', function (): void {
    $this->artisan("rm 1 999 --force --file {$this->testFile}")
        ->expectsOutputToContain('Removed 1 task(s)')
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->not->toContain('Task one');
    expect($content)->toContain('Task two');
});
