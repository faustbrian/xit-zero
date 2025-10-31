<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\Services\FileDiscovery;

beforeEach(function (): void {
    $this->tempDir = sys_get_temp_dir().'/file_discovery_test_'.uniqid();
    mkdir($this->tempDir);
});

afterEach(function (): void {
    // Clean up temp directory and all its contents
    if (is_dir($this->tempDir)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($this->tempDir);
    }
});

it('returns single file when path is a xit file', function (): void {
    // Arrange
    $filePath = $this->tempDir.'/test.xit';
    file_put_contents($filePath, 'test content');

    // Act
    $result = FileDiscovery::findXitFiles($filePath);

    // Assert
    expect($result)->toBe([$filePath]);
});

it('returns empty array when path does not exist', function (): void {
    // Arrange
    $nonExistentPath = $this->tempDir.'/does-not-exist';

    // Act
    $result = FileDiscovery::findXitFiles($nonExistentPath);

    // Assert
    expect($result)->toBe([]);
});

it('returns empty array when path is non-xit file', function (): void {
    // Arrange
    $filePath = $this->tempDir.'/test.txt';
    file_put_contents($filePath, 'test content');

    // Act
    $result = FileDiscovery::findXitFiles($filePath);

    // Assert
    expect($result)->toBe([]);
});

it('finds xit files in directory without subdirectories', function (): void {
    // Arrange
    file_put_contents($this->tempDir.'/file1.xit', 'content1');
    file_put_contents($this->tempDir.'/file2.xit', 'content2');
    file_put_contents($this->tempDir.'/file3.txt', 'content3'); // Non-.xit file

    // Act
    $result = FileDiscovery::findXitFiles($this->tempDir, false);

    // Assert
    expect($result)->toHaveCount(2)
        ->and($result)->toContain($this->tempDir.'/file1.xit')
        ->and($result)->toContain($this->tempDir.'/file2.xit');
});

it('finds xit files in directory with subdirectories', function (): void {
    // Arrange
    file_put_contents($this->tempDir.'/root.xit', 'root content');

    mkdir($this->tempDir.'/subdir1');
    file_put_contents($this->tempDir.'/subdir1/sub1.xit', 'sub1 content');

    mkdir($this->tempDir.'/subdir2');
    file_put_contents($this->tempDir.'/subdir2/sub2.xit', 'sub2 content');

    mkdir($this->tempDir.'/subdir2/nested');
    file_put_contents($this->tempDir.'/subdir2/nested/nested.xit', 'nested content');

    // Non-.xit files
    file_put_contents($this->tempDir.'/file.txt', 'text content');
    file_put_contents($this->tempDir.'/subdir1/file.md', 'markdown content');

    // Act
    $result = FileDiscovery::findXitFiles($this->tempDir, true);

    // Assert
    expect($result)->toHaveCount(4)
        ->and($result)->toContain($this->tempDir.'/root.xit')
        ->and($result)->toContain($this->tempDir.'/subdir1/sub1.xit')
        ->and($result)->toContain($this->tempDir.'/subdir2/sub2.xit')
        ->and($result)->toContain($this->tempDir.'/subdir2/nested/nested.xit');
});

it('excludes subdirectories when includeSubdirs is false', function (): void {
    // Arrange
    file_put_contents($this->tempDir.'/root.xit', 'root content');

    mkdir($this->tempDir.'/subdir');
    file_put_contents($this->tempDir.'/subdir/sub.xit', 'sub content');

    // Act
    $result = FileDiscovery::findXitFiles($this->tempDir, false);

    // Assert
    expect($result)->toHaveCount(1)
        ->and($result)->toContain($this->tempDir.'/root.xit')
        ->and($result)->not->toContain($this->tempDir.'/subdir/sub.xit');
});

it('sorts files alphabetically', function (): void {
    // Arrange
    file_put_contents($this->tempDir.'/zebra.xit', 'z');
    file_put_contents($this->tempDir.'/alpha.xit', 'a');
    file_put_contents($this->tempDir.'/beta.xit', 'b');

    // Act
    $result = FileDiscovery::findXitFiles($this->tempDir, false);

    // Assert
    expect($result)->toBe([
        $this->tempDir.'/alpha.xit',
        $this->tempDir.'/beta.xit',
        $this->tempDir.'/zebra.xit',
    ]);
});

it('filters only xit extension files', function (): void {
    // Arrange
    file_put_contents($this->tempDir.'/file.xit', 'xit');
    file_put_contents($this->tempDir.'/file.txt', 'txt');
    file_put_contents($this->tempDir.'/file.md', 'md');
    file_put_contents($this->tempDir.'/file.xit.bak', 'backup'); // Ends with .bak, not .xit

    // Act
    $result = FileDiscovery::findXitFiles($this->tempDir, false);

    // Assert
    expect($result)->toHaveCount(1)
        ->and($result)->toContain($this->tempDir.'/file.xit');
});

it('uses current working directory when path is null', function (): void {
    // Arrange
    $originalCwd = getcwd();
    chdir($this->tempDir);
    file_put_contents('test.xit', 'content');

    // Act
    $result = FileDiscovery::findXitFiles(null, false);

    // Assert
    expect($result)->toHaveCount(1)
        ->and($result[0])->toEndWith('test.xit');

    // Cleanup
    chdir($originalCwd);
});

it('returns empty array for empty directory', function (): void {
    // Arrange - directory is already empty from beforeEach

    // Act
    $result = FileDiscovery::findXitFiles($this->tempDir, false);

    // Assert
    expect($result)->toBe([]);
});

it('returns empty array for empty directory with subdirectories enabled', function (): void {
    // Arrange - directory is already empty from beforeEach

    // Act
    $result = FileDiscovery::findXitFiles($this->tempDir, true);

    // Assert
    expect($result)->toBe([]);
});

it('handles deep nested directory structures', function (): void {
    // Arrange
    $deepPath = $this->tempDir;
    for ($i = 1; $i <= 5; $i++) {
        $deepPath .= '/level'.$i;
        mkdir($deepPath);
    }
    file_put_contents($deepPath.'/deep.xit', 'deep content');

    // Act
    $result = FileDiscovery::findXitFiles($this->tempDir, true);

    // Assert
    expect($result)->toHaveCount(1)
        ->and($result)->toContain($deepPath.'/deep.xit');
});
