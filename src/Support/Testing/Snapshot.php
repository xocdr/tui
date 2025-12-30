<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Testing;

use PHPUnit\Framework\TestCase;

/**
 * Snapshot testing utility for TUI components.
 *
 * Captures rendered output and compares against stored snapshots.
 * Supports automatic snapshot creation and updating via environment variable.
 *
 * @example
 * $snapshot = new Snapshot($this, 'my-component');
 * $snapshot->assertMatches($renderer->getOutput());
 *
 * // To update snapshots:
 * // UPDATE_SNAPSHOTS=1 ./vendor/bin/phpunit
 */
class Snapshot
{
    private TestCase $testCase;

    private string $name;

    private string $directory;

    /**
     * @param TestCase $testCase The PHPUnit test case
     * @param string $name Snapshot name (used as filename)
     */
    public function __construct(TestCase $testCase, string $name)
    {
        $this->testCase = $testCase;
        $this->name = $this->sanitizeName($name);

        // Store snapshots next to test file in .tui-snapshots directory
        $reflection = new \ReflectionClass($testCase);
        $testDir = dirname((string) $reflection->getFileName());
        $this->directory = $testDir . '/.tui-snapshots';
    }

    /**
     * Assert that the actual output matches the stored snapshot.
     *
     * If no snapshot exists, creates one and marks test as incomplete.
     * If UPDATE_SNAPSHOTS env var is set, updates the snapshot.
     */
    public function assertMatches(string $actual): void
    {
        $snapshotFile = $this->getSnapshotPath();

        if (!file_exists($snapshotFile)) {
            $this->writeSnapshot($actual);
            $this->testCase->markTestIncomplete(
                "Snapshot created: {$this->name}. Run tests again to verify."
            );

            return;
        }

        $expected = $this->readSnapshot();

        if ($expected !== $actual) {
            if ($this->shouldUpdate()) {
                $this->writeSnapshot($actual);

                return;
            }

            $this->testCase->fail(
                "Snapshot mismatch for '{$this->name}':\n\n" .
                $this->generateDiff($expected, $actual) .
                "\n\nTo update snapshot, run: UPDATE_SNAPSHOTS=1 ./vendor/bin/phpunit"
            );
        }
    }

    /**
     * Get the path to the snapshot file.
     */
    public function getSnapshotPath(): string
    {
        return $this->directory . '/' . $this->name . '.snap';
    }

    /**
     * Check if the snapshot exists.
     */
    public function exists(): bool
    {
        return file_exists($this->getSnapshotPath());
    }

    /**
     * Delete the snapshot file.
     */
    public function delete(): bool
    {
        $path = $this->getSnapshotPath();
        if (file_exists($path)) {
            return unlink($path);
        }

        return false;
    }

    /**
     * Write the snapshot file.
     *
     * @throws \RuntimeException If directory creation or file write fails
     */
    private function writeSnapshot(string $content): void
    {
        if (!is_dir($this->directory)) {
            if (!mkdir($this->directory, 0755, true) && !is_dir($this->directory)) {
                throw new \RuntimeException(
                    sprintf('Failed to create snapshot directory: %s', $this->directory)
                );
            }
        }

        $header = $this->generateHeader($content);
        $path = $this->getSnapshotPath();
        $result = file_put_contents($path, $header . $content);

        if ($result === false) {
            throw new \RuntimeException(
                sprintf('Failed to write snapshot file: %s', $path)
            );
        }
    }

    /**
     * Read the snapshot file content (without header).
     */
    private function readSnapshot(): string
    {
        $content = file_get_contents($this->getSnapshotPath());
        if ($content === false) {
            return '';
        }

        // Skip header (3 lines: name, checksum, separator)
        $lines = explode("\n", $content);
        if (count($lines) >= 3 && str_starts_with($lines[0], '--- Snapshot:')) {
            return implode("\n", array_slice($lines, 3));
        }

        // No header, return as-is
        return $content;
    }

    /**
     * Generate the snapshot file header.
     */
    private function generateHeader(string $content): string
    {
        $checksum = md5($content);
        $timestamp = date('Y-m-d H:i:s');

        return <<<HEADER
        --- Snapshot: {$this->name} ---
        Checksum: {$checksum} | Created: {$timestamp}
        ---

        HEADER;
    }

    /**
     * Check if snapshots should be updated.
     */
    private function shouldUpdate(): bool
    {
        return getenv('UPDATE_SNAPSHOTS') !== false
            && getenv('UPDATE_SNAPSHOTS') !== ''
            && getenv('UPDATE_SNAPSHOTS') !== '0';
    }

    /**
     * Generate a diff between expected and actual content.
     */
    private function generateDiff(string $expected, string $actual): string
    {
        $expectedLines = explode("\n", $expected);
        $actualLines = explode("\n", $actual);

        $diff = [];
        $diff[] = 'Expected vs Actual:';
        $diff[] = str_repeat('-', 40);

        $maxLines = max(count($expectedLines), count($actualLines));
        $diffCount = 0;

        for ($i = 0; $i < $maxLines; $i++) {
            $exp = $expectedLines[$i] ?? '<missing>';
            $act = $actualLines[$i] ?? '<missing>';

            if ($exp !== $act) {
                $diffCount++;
                $lineNum = $i + 1;
                $diff[] = "Line {$lineNum}:";
                $diff[] = "  - {$exp}";
                $diff[] = "  + {$act}";

                // Limit diff output
                if ($diffCount >= 10) {
                    $remaining = $maxLines - $i - 1;
                    if ($remaining > 0) {
                        $diff[] = "... and {$remaining} more lines differ";
                    }
                    break;
                }
            }
        }

        if ($diffCount === 0) {
            $diff[] = '(No visible line differences - check whitespace/encoding)';
        }

        return implode("\n", $diff);
    }

    /**
     * Sanitize the snapshot name for use as a filename.
     */
    private function sanitizeName(string $name): string
    {
        // Replace non-alphanumeric chars with dashes
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '-', $name);

        // Remove consecutive dashes
        $sanitized = preg_replace('/-+/', '-', $sanitized ?? $name);

        // Trim dashes from ends
        return trim($sanitized ?? $name, '-');
    }
}
