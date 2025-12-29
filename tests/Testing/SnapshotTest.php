<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Testing;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Testing\Snapshot;

class SnapshotTest extends TestCase
{
    private string $snapshotDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->snapshotDir = __DIR__ . '/.tui-snapshots';

        // Clean up any existing test snapshots
        if (is_dir($this->snapshotDir)) {
            $files = glob($this->snapshotDir . '/test-*.snap');
            foreach ($files ?: [] as $file) {
                unlink($file);
            }
        }
    }

    protected function tearDown(): void
    {
        // Clean up test snapshots
        if (is_dir($this->snapshotDir)) {
            $files = glob($this->snapshotDir . '/test-*.snap');
            foreach ($files ?: [] as $file) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    public function testGetSnapshotPath(): void
    {
        $snapshot = new Snapshot($this, 'test-path');
        $path = $snapshot->getSnapshotPath();

        $this->assertStringEndsWith('.tui-snapshots/test-path.snap', $path);
    }

    public function testSanitizesName(): void
    {
        $snapshot = new Snapshot($this, 'test with spaces/and:special!chars');
        $path = $snapshot->getSnapshotPath();

        $this->assertStringEndsWith('test-with-spaces-and-special-chars.snap', $path);
    }

    public function testExistsReturnsFalseForNewSnapshot(): void
    {
        $snapshot = new Snapshot($this, 'test-nonexistent');

        $this->assertFalse($snapshot->exists());
    }

    public function testDeleteReturnsFalseForNonexistent(): void
    {
        $snapshot = new Snapshot($this, 'test-nonexistent-delete');

        $this->assertFalse($snapshot->delete());
    }

    public function testSnapshotCreation(): void
    {
        $snapshot = new Snapshot($this, 'test-creation');

        // First assertion creates the snapshot
        try {
            $snapshot->assertMatches('Test content');
            $this->fail('Expected test to be marked incomplete');
        } catch (\PHPUnit\Framework\IncompleteTestError $e) {
            $this->assertStringContainsString('Snapshot created', $e->getMessage());
        }

        // Snapshot file should exist now
        $this->assertTrue($snapshot->exists());

        // Content should be readable
        $content = file_get_contents($snapshot->getSnapshotPath());
        $this->assertStringContainsString('Test content', $content);
        $this->assertStringContainsString('Checksum:', $content);
    }

    public function testSnapshotMatches(): void
    {
        $snapshot = new Snapshot($this, 'test-matches');

        // Create initial snapshot
        try {
            $snapshot->assertMatches('Matching content');
        } catch (\PHPUnit\Framework\IncompleteTestError $e) {
            // Expected on first run
        }

        // Second assertion should pass with same content
        $snapshot->assertMatches('Matching content');

        // No exception = test passed
        $this->assertTrue(true);
    }

    public function testSnapshotMismatch(): void
    {
        $snapshot = new Snapshot($this, 'test-mismatch');

        // Create initial snapshot
        try {
            $snapshot->assertMatches('Original content');
        } catch (\PHPUnit\Framework\IncompleteTestError $e) {
            // Expected on first run
        }

        // Different content should fail
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Snapshot mismatch');

        $snapshot->assertMatches('Different content');
    }

    public function testSnapshotUpdate(): void
    {
        $snapshot = new Snapshot($this, 'test-update');

        // Create initial snapshot
        try {
            $snapshot->assertMatches('Original content');
        } catch (\PHPUnit\Framework\IncompleteTestError $e) {
            // Expected
        }

        // Set UPDATE_SNAPSHOTS env var
        $originalEnv = getenv('UPDATE_SNAPSHOTS');
        putenv('UPDATE_SNAPSHOTS=1');

        try {
            // Should update instead of failing
            $snapshot->assertMatches('Updated content');

            // Verify content was updated
            $content = file_get_contents($snapshot->getSnapshotPath());
            $this->assertStringContainsString('Updated content', $content);
        } finally {
            // Restore env var
            if ($originalEnv === false) {
                putenv('UPDATE_SNAPSHOTS');
            } else {
                putenv('UPDATE_SNAPSHOTS=' . $originalEnv);
            }
        }
    }

    public function testDeleteRemovesSnapshot(): void
    {
        $snapshot = new Snapshot($this, 'test-delete');

        // Create snapshot
        try {
            $snapshot->assertMatches('To be deleted');
        } catch (\PHPUnit\Framework\IncompleteTestError $e) {
            // Expected
        }

        $this->assertTrue($snapshot->exists());

        // Delete it
        $result = $snapshot->delete();

        $this->assertTrue($result);
        $this->assertFalse($snapshot->exists());
    }
}
