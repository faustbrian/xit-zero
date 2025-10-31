<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

beforeEach(function (): void {
    $this->testFile = createTestFile('test.xit', "[ ] Task one\n[ ] Task two\n[ ] Task three");
    $this->targetFile = sys_get_temp_dir().'/target.xit';
});

afterEach(function (): void {
    cleanupTestFile($this->testFile);
    cleanupTestFile($this->targetFile);
});

it('moves task to new file', function (): void {
    $this->artisan("move 1 --target {$this->targetFile} --file {$this->testFile}")
        ->expectsOutputToContain('Moved')
        ->assertExitCode(0);

    $sourceContent = file_get_contents($this->testFile);
    expect($sourceContent)->not->toContain('Task one');

    $targetContent = file_get_contents($this->targetFile);
    expect($targetContent)->toContain('Task one');
});

it('moves multiple tasks', function (): void {
    $this->artisan("move 1 2 --target {$this->targetFile} --file {$this->testFile}")
        ->expectsOutputToContain('2 task(s)')
        ->assertExitCode(0);

    $targetContent = file_get_contents($this->targetFile);
    expect($targetContent)->toContain('Task one');
    expect($targetContent)->toContain('Task two');
});

it('moves task to existing file', function (): void {
    file_put_contents($this->targetFile, "[ ] Existing task\n");

    $this->artisan("move 1 --target {$this->targetFile} --file {$this->testFile}")
        ->assertExitCode(0);

    $targetContent = file_get_contents($this->targetFile);
    expect($targetContent)->toContain('Existing task');
    expect($targetContent)->toContain('Task one');
});

it('requires target flag', function (): void {
    $this->artisan("move 1 --file {$this->testFile}")
        ->assertExitCode(1);
});
