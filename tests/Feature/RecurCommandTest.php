<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

beforeEach(function (): void {
    $this->testFile = createTestFile('test.xit', '[ ] Task one -> 2025-12-01');
    $this->targetFile = sys_get_temp_dir().'/recur-target.xit';
});

afterEach(function (): void {
    cleanupTestFile($this->testFile);
    cleanupTestFile($this->targetFile);
});

it('creates weekly recurring instances', function (): void {
    $this->artisan("recur 1 --interval 1w --count 3 --file {$this->testFile}")
        ->expectsOutputToContain('3 recurring instance(s)')
        ->assertExitCode(0);
});

it('creates daily recurring instances', function (): void {
    $this->artisan("recur 1 --interval 1d --count 5 --target {$this->targetFile} --file {$this->testFile}")
        ->expectsOutputToContain('5 recurring instance(s)')
        ->assertExitCode(0);

    expect(file_exists($this->targetFile))->toBeTrue();
});

it('creates monthly recurring instances', function (): void {
    $this->artisan("recur 1 --interval 1m --count 2 --file {$this->testFile}")
        ->assertExitCode(0);
});

it('creates yearly recurring instances', function (): void {
    $this->artisan("recur 1 --interval 1y --count 2 --file {$this->testFile}")
        ->assertExitCode(0);
});

it('creates instances until end date', function (): void {
    $this->artisan("recur 1 --interval 1w --end 2025-12-31 --file {$this->testFile}")
        ->assertExitCode(0);
});

it('requires interval flag', function (): void {
    $this->artisan("recur 1 --count 3 --file {$this->testFile}")
        ->assertExitCode(1);
});

it('requires count or end', function (): void {
    $this->artisan("recur 1 --interval 1w --file {$this->testFile}")
        ->assertExitCode(1);
});

it('rejects invalid interval format', function (): void {
    $this->artisan("recur 1 --interval invalid --count 3 --file {$this->testFile}")
        ->assertExitCode(1);
});
