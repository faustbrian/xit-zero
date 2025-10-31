<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

beforeEach(function (): void {
    $this->testFile = createTestFile('test.xit', "[ ] Task one\n[ ] Task two #existing");
});

afterEach(function (): void {
    cleanupTestFile($this->testFile);
});

it('adds tag to task', function (): void {
    $this->artisan("tag 1 urgent --file {$this->testFile}")
        ->expectsOutputToContain('Added tag')
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('#urgent');
});

it('adds tag with hash symbol', function (): void {
    $this->artisan("tag 1 #work --file {$this->testFile}")
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('#work');
});

it('handles tag already present', function (): void {
    $this->artisan("tag 2 existing --file {$this->testFile}")
        ->expectsOutputToContain('already has tag')
        ->assertExitCode(0);
});

it('handles invalid task ID', function (): void {
    $this->artisan("tag 999 urgent --file {$this->testFile}")
        ->expectsOutputToContain('Task 999 not found')
        ->assertExitCode(1);
});

