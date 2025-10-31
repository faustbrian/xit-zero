<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

beforeEach(function (): void {
    $this->testFile = createTestFile('test.xit', "[ ] Task one\n[ ] ! Task two");
});

afterEach(function (): void {
    cleanupTestFile($this->testFile);
});

it('sets task priority to 1', function (): void {
    $this->artisan("prio 1 1 --file {$this->testFile}")
        ->expectsOutputToContain('priority to 1')
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('[ ] ! Task one');
});

it('sets task priority to 2', function (): void {
    $this->artisan("prio 1 2 --file {$this->testFile}")
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('[ ] !! Task one');
});

it('sets task priority to 3', function (): void {
    $this->artisan("prio 1 3 --file {$this->testFile}")
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('[ ] !!! Task one');
});

it('removes priority with 0', function (): void {
    $this->artisan("prio 2 0 --file {$this->testFile}")
        ->expectsOutputToContain('Removed priority')
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('[ ] Task two');
    expect($content)->not->toContain('!');
});

it('rejects priority above 3', function (): void {
    $this->artisan("prio 1 4 --file {$this->testFile}")
        ->expectsOutputToContain('Priority must be between 0 and 3')
        ->assertExitCode(1);
});

it('handles task not found', function (): void {
    $this->artisan("prio 999 1 --file {$this->testFile}")
        ->expectsOutputToContain('Task 999 not found')
        ->assertExitCode(1);
});
