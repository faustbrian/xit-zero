<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\Parser\Task;
use App\Parser\XitFile;
use App\Services\TaskIndex;
use App\Services\TaskReference;

describe('TaskIndex', function (): void {
    beforeEach(function (): void {
        // Create temporary test files
        $this->tempDir = sys_get_temp_dir().'/xit_test_'.uniqid();
        mkdir($this->tempDir);

        $this->validFile1 = $this->tempDir.'/tasks1.xit';
        $this->validFile2 = $this->tempDir.'/tasks2.xit';
        $this->invalidFile = $this->tempDir.'/invalid.xit';

        // Valid file 1 with 2 tasks
        file_put_contents($this->validFile1, "Work\n[ ] Task one\n[x] Task two");

        // Valid file 2 with 1 task
        file_put_contents($this->validFile2, "Personal\n[ ] Task three");

        // Create an unreadable file for testing file_get_contents failure
        // Note: We'll handle this by using a non-existent path
        $this->invalidFile = $this->tempDir.'/nonexistent.xit';
    });

    afterEach(function (): void {
        // Cleanup temporary files
        if (file_exists($this->validFile1)) {
            unlink($this->validFile1);
        }

        if (file_exists($this->validFile2)) {
            unlink($this->validFile2);
        }

        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
    });

    it('creates index from valid file paths', function (): void {
        // Arrange & Act
        $index = new TaskIndex([$this->validFile1, $this->validFile2]);

        // Assert
        expect($index->getFiles())->toHaveCount(2);
        expect($index->getReferences())->toHaveCount(3); // 2 from file1, 1 from file2
    });

    it('skips files that cannot be read', function (): void {
        // Arrange & Act
        // Note: file_get_contents will produce a warning for non-existent file,
        // but the TaskIndex correctly handles the false return value with continue
        set_error_handler(function (): void {
            // Suppress expected warning
        });

        $index = new TaskIndex([
            $this->validFile1,
            $this->invalidFile, // This file doesn't exist, so file_get_contents returns false
            $this->validFile2,
        ]);

        restore_error_handler();

        // Assert - should only have the 2 valid files
        expect($index->getFiles())->toHaveCount(2);
        expect($index->getReferences())->toHaveCount(3);
    });

    it('handles empty file list', function (): void {
        // Arrange & Act
        $index = new TaskIndex([]);

        // Assert
        expect($index->getFiles())->toHaveCount(0);
        expect($index->getReferences())->toHaveCount(0);
    });

    it('handles file with no tasks', function (): void {
        // Arrange
        $emptyFile = $this->tempDir.'/empty.xit';
        file_put_contents($emptyFile, "Work\n\nPersonal");

        // Act
        $index = new TaskIndex([$emptyFile]);

        // Assert
        expect($index->getFiles())->toHaveCount(1);
        expect($index->getReferences())->toHaveCount(0);

        unlink($emptyFile);
    });
});

describe('TaskIndex::getFiles', function (): void {
    beforeEach(function (): void {
        $this->tempDir = sys_get_temp_dir().'/xit_test_'.uniqid();
        mkdir($this->tempDir);

        $this->file1 = $this->tempDir.'/tasks1.xit';
        $this->file2 = $this->tempDir.'/tasks2.xit';

        file_put_contents($this->file1, '[ ] Task one');
        file_put_contents($this->file2, '[ ] Task two');
    });

    afterEach(function (): void {
        if (file_exists($this->file1)) {
            unlink($this->file1);
        }

        if (file_exists($this->file2)) {
            unlink($this->file2);
        }

        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
    });

    it('returns all parsed XitFile objects', function (): void {
        // Arrange
        $index = new TaskIndex([$this->file1, $this->file2]);

        // Act
        $files = $index->getFiles();

        // Assert
        expect($files)->toHaveCount(2);
        expect($files[0])->toBeInstanceOf(XitFile::class);
        expect($files[1])->toBeInstanceOf(XitFile::class);
        expect($files[0]->path)->toBe($this->file1);
        expect($files[1]->path)->toBe($this->file2);
    });

    it('returns empty array when no files were parsed', function (): void {
        // Arrange
        $index = new TaskIndex([]);

        // Act
        $files = $index->getFiles();

        // Assert
        expect($files)->toHaveCount(0);
        expect($files)->toBe([]);
    });
});

describe('TaskIndex::getReferences', function (): void {
    beforeEach(function (): void {
        $this->tempDir = sys_get_temp_dir().'/xit_test_'.uniqid();
        mkdir($this->tempDir);

        $this->file = $this->tempDir.'/tasks.xit';
        file_put_contents($this->file, "Work\n[ ] Task one\n[x] Task two\n\nPersonal\n[@] Task three");
    });

    afterEach(function (): void {
        if (file_exists($this->file)) {
            unlink($this->file);
        }

        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
    });

    it('returns all TaskReference objects', function (): void {
        // Arrange
        $index = new TaskIndex([$this->file]);

        // Act
        $references = $index->getReferences();

        // Assert
        expect($references)->toHaveCount(3);
        expect($references[0])->toBeInstanceOf(TaskReference::class);
        expect($references[1])->toBeInstanceOf(TaskReference::class);
        expect($references[2])->toBeInstanceOf(TaskReference::class);
    });

    it('contains correct task data in references', function (): void {
        // Arrange
        $index = new TaskIndex([$this->file]);

        // Act
        $references = $index->getReferences();

        // Assert
        expect($references[0]->task->description)->toBe('Task one');
        expect($references[0]->task->status)->toBe('open');
        expect($references[1]->task->description)->toBe('Task two');
        expect($references[1]->task->status)->toBe('checked');
        expect($references[2]->task->description)->toBe('Task three');
        expect($references[2]->task->status)->toBe('ongoing');
    });

    it('contains correct group and task indices', function (): void {
        // Arrange
        $index = new TaskIndex([$this->file]);

        // Act
        $references = $index->getReferences();

        // Assert
        // First group (Work) has tasks at indices 0 and 1
        expect($references[0]->groupIndex)->toBe(0);
        expect($references[0]->taskIndex)->toBe(0);
        expect($references[1]->groupIndex)->toBe(0);
        expect($references[1]->taskIndex)->toBe(1);

        // Second group (Personal) has task at index 0
        expect($references[2]->groupIndex)->toBe(1);
        expect($references[2]->taskIndex)->toBe(0);
    });

    it('returns empty array when no tasks exist', function (): void {
        // Arrange
        $index = new TaskIndex([]);

        // Act
        $references = $index->getReferences();

        // Assert
        expect($references)->toHaveCount(0);
        expect($references)->toBe([]);
    });
});

describe('TaskIndex::getTaskByIndex', function (): void {
    beforeEach(function (): void {
        $this->tempDir = sys_get_temp_dir().'/xit_test_'.uniqid();
        mkdir($this->tempDir);

        $this->file = $this->tempDir.'/tasks.xit';
        file_put_contents($this->file, "[ ] Task one\n[x] Task two\n[@] Task three");

        $this->index = new TaskIndex([$this->file]);
    });

    afterEach(function (): void {
        if (file_exists($this->file)) {
            unlink($this->file);
        }

        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
    });

    it('returns task reference for valid index', function (): void {
        // Arrange & Act
        $task = $this->index->getTaskByIndex(1);

        // Assert
        expect($task)->toBeInstanceOf(TaskReference::class);
        expect($task->task->description)->toBe('Task one');
    });

    it('returns correct task for index 2', function (): void {
        // Arrange & Act
        $task = $this->index->getTaskByIndex(2);

        // Assert
        expect($task)->toBeInstanceOf(TaskReference::class);
        expect($task->task->description)->toBe('Task two');
    });

    it('returns correct task for index 3', function (): void {
        // Arrange & Act
        $task = $this->index->getTaskByIndex(3);

        // Assert
        expect($task)->toBeInstanceOf(TaskReference::class);
        expect($task->task->description)->toBe('Task three');
    });

    it('returns null for index 0', function (): void {
        // Arrange & Act
        $task = $this->index->getTaskByIndex(0);

        // Assert
        expect($task)->toBeNull();
    });

    it('returns null for negative index', function (): void {
        // Arrange & Act
        $task = $this->index->getTaskByIndex(-1);

        // Assert
        expect($task)->toBeNull();
    });

    it('returns null for index beyond available tasks', function (): void {
        // Arrange & Act
        $task = $this->index->getTaskByIndex(999);

        // Assert
        expect($task)->toBeNull();
    });

    it('uses 1-based indexing', function (): void {
        // Arrange & Act
        $firstTask = $this->index->getTaskByIndex(1);
        $zeroTask = $this->index->getTaskByIndex(0);

        // Assert
        expect($firstTask)->not->toBeNull();
        expect($firstTask->task->description)->toBe('Task one');
        expect($zeroTask)->toBeNull();
    });
});

describe('TaskIndex::getTasksByIndices', function (): void {
    beforeEach(function (): void {
        $this->tempDir = sys_get_temp_dir().'/xit_test_'.uniqid();
        mkdir($this->tempDir);

        $this->file = $this->tempDir.'/tasks.xit';
        file_put_contents($this->file, "[ ] Task one\n[x] Task two\n[@] Task three\n[~] Task four");

        $this->index = new TaskIndex([$this->file]);
    });

    afterEach(function (): void {
        if (file_exists($this->file)) {
            unlink($this->file);
        }

        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
    });

    it('returns tasks for valid indices', function (): void {
        // Arrange & Act
        $tasks = $this->index->getTasksByIndices([1, 3]);

        // Assert
        expect($tasks)->toHaveCount(2);
        expect($tasks[0]->task->description)->toBe('Task one');
        expect($tasks[1]->task->description)->toBe('Task three');
    });

    it('skips invalid indices', function (): void {
        // Arrange & Act
        $tasks = $this->index->getTasksByIndices([1, 999, 2]);

        // Assert
        expect($tasks)->toHaveCount(2);
        expect($tasks[0]->task->description)->toBe('Task one');
        expect($tasks[1]->task->description)->toBe('Task two');
    });

    it('returns empty array for all invalid indices', function (): void {
        // Arrange & Act
        $tasks = $this->index->getTasksByIndices([0, -1, 999]);

        // Assert
        expect($tasks)->toHaveCount(0);
        expect($tasks)->toBe([]);
    });

    it('returns empty array for empty indices array', function (): void {
        // Arrange & Act
        $tasks = $this->index->getTasksByIndices([]);

        // Assert
        expect($tasks)->toHaveCount(0);
        expect($tasks)->toBe([]);
    });

    it('handles duplicate indices', function (): void {
        // Arrange & Act
        $tasks = $this->index->getTasksByIndices([1, 1, 2]);

        // Assert
        expect($tasks)->toHaveCount(3); // Will return duplicates
        expect($tasks[0]->task->description)->toBe('Task one');
        expect($tasks[1]->task->description)->toBe('Task one');
        expect($tasks[2]->task->description)->toBe('Task two');
    });

    it('preserves order of requested indices', function (): void {
        // Arrange & Act
        $tasks = $this->index->getTasksByIndices([3, 1, 2]);

        // Assert
        expect($tasks)->toHaveCount(3);
        expect($tasks[0]->task->description)->toBe('Task three');
        expect($tasks[1]->task->description)->toBe('Task one');
        expect($tasks[2]->task->description)->toBe('Task two');
    });
});

describe('TaskIndex::getAllTasks', function (): void {
    beforeEach(function (): void {
        $this->tempDir = sys_get_temp_dir().'/xit_test_'.uniqid();
        mkdir($this->tempDir);

        $this->file1 = $this->tempDir.'/tasks1.xit';
        $this->file2 = $this->tempDir.'/tasks2.xit';

        file_put_contents($this->file1, "[ ] Task one\n[x] Task two");
        file_put_contents($this->file2, '[@] Task three');
    });

    afterEach(function (): void {
        if (file_exists($this->file1)) {
            unlink($this->file1);
        }

        if (file_exists($this->file2)) {
            unlink($this->file2);
        }

        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
    });

    it('returns all task references', function (): void {
        // Arrange
        $index = new TaskIndex([$this->file1, $this->file2]);

        // Act
        $tasks = $index->getAllTasks();

        // Assert
        expect($tasks)->toHaveCount(3);
        expect($tasks[0]->task->description)->toBe('Task one');
        expect($tasks[1]->task->description)->toBe('Task two');
        expect($tasks[2]->task->description)->toBe('Task three');
    });

    it('returns same array as getReferences', function (): void {
        // Arrange
        $index = new TaskIndex([$this->file1, $this->file2]);

        // Act
        $allTasks = $index->getAllTasks();
        $references = $index->getReferences();

        // Assert
        expect($allTasks)->toBe($references);
    });

    it('returns empty array when no tasks exist', function (): void {
        // Arrange
        $index = new TaskIndex([]);

        // Act
        $tasks = $index->getAllTasks();

        // Assert
        expect($tasks)->toHaveCount(0);
        expect($tasks)->toBe([]);
    });
});

describe('TaskReference', function (): void {
    it('stores file reference correctly', function (): void {
        // Arrange
        $tempDir = sys_get_temp_dir().'/xit_test_'.uniqid();
        mkdir($tempDir);
        $file = $tempDir.'/tasks.xit';
        file_put_contents($file, '[ ] Task one');

        $index = new TaskIndex([$file]);

        // Act
        $reference = $index->getTaskByIndex(1);

        // Assert
        expect($reference->file)->toBeInstanceOf(XitFile::class);
        expect($reference->file->path)->toBe($file);

        unlink($file);
        rmdir($tempDir);
    });

    it('stores group index correctly', function (): void {
        // Arrange
        $tempDir = sys_get_temp_dir().'/xit_test_'.uniqid();
        mkdir($tempDir);
        $file = $tempDir.'/tasks.xit';
        file_put_contents($file, "Work\n[ ] Task one\n\nPersonal\n[ ] Task two");

        $index = new TaskIndex([$file]);

        // Act
        $ref1 = $index->getTaskByIndex(1);
        $ref2 = $index->getTaskByIndex(2);

        // Assert
        expect($ref1->groupIndex)->toBe(0); // First group
        expect($ref2->groupIndex)->toBe(1); // Second group

        unlink($file);
        rmdir($tempDir);
    });

    it('stores task index correctly', function (): void {
        // Arrange
        $tempDir = sys_get_temp_dir().'/xit_test_'.uniqid();
        mkdir($tempDir);
        $file = $tempDir.'/tasks.xit';
        file_put_contents($file, "[ ] Task one\n[x] Task two\n[@] Task three");

        $index = new TaskIndex([$file]);

        // Act
        $ref1 = $index->getTaskByIndex(1);
        $ref2 = $index->getTaskByIndex(2);
        $ref3 = $index->getTaskByIndex(3);

        // Assert
        expect($ref1->taskIndex)->toBe(0);
        expect($ref2->taskIndex)->toBe(1);
        expect($ref3->taskIndex)->toBe(2);

        unlink($file);
        rmdir($tempDir);
    });

    it('stores task object correctly', function (): void {
        // Arrange
        $tempDir = sys_get_temp_dir().'/xit_test_'.uniqid();
        mkdir($tempDir);
        $file = $tempDir.'/tasks.xit';
        file_put_contents($file, '[x] Completed task');

        $index = new TaskIndex([$file]);

        // Act
        $reference = $index->getTaskByIndex(1);

        // Assert
        expect($reference->task)->toBeInstanceOf(Task::class);
        expect($reference->task->description)->toBe('Completed task');
        expect($reference->task->status)->toBe('checked');

        unlink($file);
        rmdir($tempDir);
    });
});
