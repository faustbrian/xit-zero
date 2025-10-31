<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

beforeEach(function (): void {
    $this->testFile = createTestFile('test.xit', "[ ] Task one\n[ ] Task two");
});

afterEach(function (): void {
    cleanupTestFile($this->testFile);
});

it('edits task description', function (): void {
    $this->artisan("edit 1 \"Updated description\" --file {$this->testFile}")
        ->expectsOutputToContain('Updated')
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('Updated description');
    expect($content)->not->toContain('Task one');
});

it('handles invalid task ID', function (): void {
    $this->artisan("edit 999 \"New text\" --file {$this->testFile}")
        ->assertExitCode(1);
});
