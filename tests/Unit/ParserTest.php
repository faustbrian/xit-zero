<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\Parser\XitParser;

it('parses open checkbox', function (): void {
    $content = '[ ] Task';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups)->toHaveCount(1);
    expect($file->groups[0]->tasks[0]->status)->toBe('open');
    expect($file->groups[0]->tasks[0]->description)->toBe('Task');
});

it('parses checked checkbox', function (): void {
    $content = '[x] Task';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->status)->toBe('checked');
});

it('parses ongoing checkbox', function (): void {
    $content = '[@] Task';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->status)->toBe('ongoing');
});

it('parses obsolete checkbox', function (): void {
    $content = '[~] Task';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->status)->toBe('obsolete');
});

it('parses inquestion checkbox', function (): void {
    $content = '[?] Task';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->status)->toBe('inquestion');
});

it('parses priority level 1', function (): void {
    $content = '[ ] ! Task';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->priority)->toBe(1);
});

it('parses priority level 2', function (): void {
    $content = '[ ] !! Task';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->priority)->toBe(2);
});

it('parses priority level 3', function (): void {
    $content = '[ ] !!! Task';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->priority)->toBe(3);
});

it('parses simple tag', function (): void {
    $content = '[ ] Task #urgent';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->tags)->toHaveCount(1);
    expect($file->groups[0]->tasks[0]->tags[0]->name)->toBe('urgent');
});

it('parses multiple tags', function (): void {
    $content = '[ ] Task #urgent #work';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->tags)->toHaveCount(2);
});

it('parses tag with value', function (): void {
    $content = '[ ] Task #priority=high';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->tags[0]->value)->toBe('high');
});

it('parses tag with quoted value', function (): void {
    $content = '[ ] Task #note="important stuff"';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->tags[0]->value)->toBe('important stuff');
});

it('parses due date', function (): void {
    $content = '[ ] Task -> 2025-12-31';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->dueDate)->not->toBeNull();
    expect($file->groups[0]->tasks[0]->dueDate->raw)->toBe('2025-12-31');
});

it('parses year-month due date', function (): void {
    $content = '[ ] Task -> 2025-12';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->dueDate)->not->toBeNull();
});

it('parses year-only due date', function (): void {
    $content = '[ ] Task -> 2025';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->dueDate)->not->toBeNull();
});

it('parses week due date', function (): void {
    $content = '[ ] Task -> 2025-W45';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->dueDate)->not->toBeNull();
});

it('parses quarter due date', function (): void {
    $content = '[ ] Task -> 2025-Q4';
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->dueDate)->not->toBeNull();
});

it('parses groups with titles', function (): void {
    $content = "Work\n[ ] Task one\n[ ] Task two";
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups)->toHaveCount(1);
    expect($file->groups[0]->title)->toBe('Work');
    expect($file->groups[0]->tasks)->toHaveCount(2);
});

it('parses multiple groups', function (): void {
    $content = "Work\n[ ] Task one\n\nPersonal\n[ ] Task two";
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups)->toHaveCount(2);
    expect($file->groups[0]->title)->toBe('Work');
    expect($file->groups[1]->title)->toBe('Personal');
});

it('parses continuation lines', function (): void {
    $content = "[ ] Task one\n    line two\n    line three";
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks[0]->continuationLines)->toHaveCount(2);
    expect($file->groups[0]->tasks[0]->continuationLines[0])->toBe('line two');
    expect($file->groups[0]->tasks[0]->continuationLines[1])->toBe('line three');
});

it('serializes file correctly', function (): void {
    $content = "[ ] Task one\n[x] Task two";
    $file = XitParser::parseFile($content, 'test.xit');
    $serialized = XitParser::serializeFile($file);

    expect($serialized)->toContain('[ ] Task one');
    expect($serialized)->toContain('[x] Task two');
});

it('preserves priorities when serializing', function (): void {
    $content = '[ ] !! Important task';
    $file = XitParser::parseFile($content, 'test.xit');
    $serialized = XitParser::serializeFile($file);

    expect($serialized)->toContain('!!');
});

it('preserves group titles when serializing', function (): void {
    $content = "Work\n[ ] Task";
    $file = XitParser::parseFile($content, 'test.xit');
    $serialized = XitParser::serializeFile($file);

    expect($serialized)->toContain('Work');
});

// Edge case tests for 100% coverage

it('applies continuation lines to previous task when new task starts', function (): void {
    // Tests line 82: currentTask->continuationLines assignment when mid-task continuations
    $content = "[ ] First task\n    continuation one\n    continuation two\n[ ] Second task";
    $file = XitParser::parseFile($content, 'test.xit');

    expect($file->groups[0]->tasks)->toHaveCount(2);
    expect($file->groups[0]->tasks[0]->continuationLines)->toHaveCount(2);
    expect($file->groups[0]->tasks[0]->continuationLines[0])->toBe('continuation one');
    expect($file->groups[0]->tasks[0]->continuationLines[1])->toBe('continuation two');
    expect($file->groups[0]->tasks[1]->continuationLines)->toHaveCount(0);
});


it('serializes multiple groups with blank lines preserved', function (): void {
    // Tests line 116: str_repeat("\n", $blankLinesBefore) when output is not empty
    $content = "Work\n[ ] Task one\n\n\nPersonal\n[ ] Task two";
    $file = XitParser::parseFile($content, 'test.xit');
    $serialized = XitParser::serializeFile($file);

    expect($file->groups)->toHaveCount(2);
    expect($serialized)->toContain("Task one\n\n\nPersonal");
});

it('serializes group with title but no tasks', function (): void {
    // Tests lines 131-132: lastLineNumber assignment when group has title but no tasks
    $content = "Group Title\n\nAnother Group\n[ ] Task";
    $file = XitParser::parseFile($content, 'test.xit');
    $serialized = XitParser::serializeFile($file);

    expect($file->groups)->toHaveCount(2);
    expect($file->groups[0]->title)->toBe('Group Title');
    expect($file->groups[0]->tasks)->toHaveCount(0);
    expect($serialized)->toContain('Group Title');
    expect($serialized)->toContain('Another Group');
});

it('serializes task with continuation lines', function (): void {
    // Tests line 278: continuation line serialization in serializeTask()
    $content = "[ ] Main task\n    continuation one\n    continuation two";
    $file = XitParser::parseFile($content, 'test.xit');
    $serialized = XitParser::serializeFile($file);

    expect($serialized)->toContain('[ ] Main task');
    expect($serialized)->toContain('    continuation one');
    expect($serialized)->toContain('    continuation two');
    expect($file->groups[0]->tasks[0]->continuationLines)->toHaveCount(2);
});
