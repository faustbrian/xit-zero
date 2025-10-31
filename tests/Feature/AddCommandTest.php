<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

afterEach(function (): void {
    if (isset($this->testFile) && file_exists($this->testFile)) {
        cleanupTestFile($this->testFile);
    }
});

it('adds a simple task', function (): void {
    $this->testFile = sys_get_temp_dir().'/add-test.xit';

    $this->artisan("add \"Buy groceries\" --file {$this->testFile}")
        ->expectsOutputToContain('Task added')
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('[ ] Buy groceries');
});

it('adds task with priority and tags', function (): void {
    $this->testFile = sys_get_temp_dir().'/add-priority.xit';

    $this->artisan("add \"!! Important task #work\" --file {$this->testFile}")
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('!! Important task #work');
});

it('adds task to existing file', function (): void {
    $this->testFile = createTestFile('existing.xit', "[ ] Existing task\n");

    $this->artisan("add \"New task\" --file {$this->testFile}")
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('Existing task');
    expect($content)->toContain('New task');
});
