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

it('marks task as done', function (): void {
    $this->artisan("mark 1 --done --file {$this->testFile}")
        ->expectsOutputToContain('Marked')
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('[x] Task one');
});

it('marks task as ongoing', function (): void {
    $this->artisan("mark 1 --ongoing --file {$this->testFile}")
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('[@] Task one');
});

it('marks task as obsolete', function (): void {
    $this->artisan("mark 1 --obsolete --file {$this->testFile}")
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('[~] Task one');
});

it('marks task as in question', function (): void {
    $this->testFile = createTestFile('inquestion.xit', '[ ] Task one');

    $this->artisan("mark 1 --inquestion --file {$this->testFile}")
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('[?] Task one');
});

it('marks task as open', function (): void {
    $this->testFile = createTestFile('done.xit', '[x] Task one');

    $this->artisan("mark 1 --open --file {$this->testFile}")
        ->assertExitCode(0);

    $content = file_get_contents($this->testFile);
    expect($content)->toContain('[ ] Task one');
});

it('marks multiple tasks', function (): void {
    $this->artisan("mark 1 2 3 --done --file {$this->testFile}")
        ->expectsOutputToContain('3 task(s)')
        ->assertExitCode(0);
});

it('requires status flag', function (): void {
    $this->artisan("mark 1 --file {$this->testFile}")
        ->assertExitCode(1);
});

it('rejects multiple status flags', function (): void {
    $this->artisan("mark 1 --done --open --file {$this->testFile}")
        ->assertExitCode(1);
});
