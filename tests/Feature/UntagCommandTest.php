<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

beforeEach(function (): void {
    $this->testFile = createTestFile('test.xit', "[ ] Task one #urgent #work\n[ ] Task two");
});

afterEach(function (): void {
    cleanupTestFile($this->testFile);
});

it('removes tag from task', function (): void {
    $this->artisan("untag 1 urgent --file {$this->testFile}")
        ->expectsOutputToContain('Removed tag')
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->not->toContain('#urgent');
    expect($content)->toContain('#work');
});

it('removes tag with hash symbol', function (): void {
    $this->artisan("untag 1 #work --file {$this->testFile}")
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->not->toContain('#work');
});

it('handles tag not present', function (): void {
    $this->artisan("untag 2 nonexistent --file {$this->testFile}")
        ->expectsOutputToContain('does not have tag')
        ->assertExitCode(0);
});

it('handles invalid task ID', function (): void {
    $this->artisan("untag 999 urgent --file {$this->testFile}")
        ->expectsOutputToContain('Task 999 not found')
        ->assertExitCode(1);
});
