<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support;

use PHPUnit\Framework\Assert;

/**
 * PHPUnit assertion trait for testing TUI components.
 */
trait TuiAssertions
{
    /**
     * Assert that the output contains a string.
     */
    public function assertOutputContains(MockInstance|TestRenderer $subject, string $needle, string $message = ''): void
    {
        $output = $subject instanceof MockInstance
            ? $subject->getLastOutput()
            : $subject->getOutput();

        Assert::assertStringContainsString(
            $needle,
            $output,
            $message ?: "Failed asserting that output contains '{$needle}'"
        );
    }

    /**
     * Assert that the output does not contain a string.
     */
    public function assertOutputNotContains(MockInstance|TestRenderer $subject, string $needle, string $message = ''): void
    {
        $output = $subject instanceof MockInstance
            ? $subject->getLastOutput()
            : $subject->getOutput();

        Assert::assertStringNotContainsString(
            $needle,
            $output,
            $message ?: "Failed asserting that output does not contain '{$needle}'"
        );
    }

    /**
     * Assert that the output matches a regex pattern.
     */
    public function assertOutputMatches(MockInstance|TestRenderer $subject, string $pattern, string $message = ''): void
    {
        $output = $subject instanceof MockInstance
            ? $subject->getLastOutput()
            : $subject->getOutput();

        Assert::assertMatchesRegularExpression(
            $pattern,
            $output,
            $message ?: "Failed asserting that output matches pattern '{$pattern}'"
        );
    }

    /**
     * Assert that the output has a specific number of lines.
     */
    public function assertOutputLineCount(MockInstance|TestRenderer $subject, int $expected, string $message = ''): void
    {
        $lines = $subject instanceof MockInstance
            ? $subject->getOutputLines()
            : $subject->getOutputLines();

        Assert::assertCount(
            $expected,
            $lines,
            $message ?: "Failed asserting that output has {$expected} lines"
        );
    }

    /**
     * Assert that a specific line contains text.
     */
    public function assertLineContains(MockInstance|TestRenderer $subject, int $lineNumber, string $needle, string $message = ''): void
    {
        $lines = $subject instanceof MockInstance
            ? $subject->getOutputLines()
            : $subject->getOutputLines();

        Assert::assertArrayHasKey(
            $lineNumber,
            $lines,
            "Line {$lineNumber} does not exist in output"
        );

        Assert::assertStringContainsString(
            $needle,
            $lines[$lineNumber],
            $message ?: "Failed asserting that line {$lineNumber} contains '{$needle}'"
        );
    }

    /**
     * Assert that the output equals expected string exactly.
     */
    public function assertOutputEquals(MockInstance|TestRenderer $subject, string $expected, string $message = ''): void
    {
        $output = $subject instanceof MockInstance
            ? $subject->getLastOutput()
            : $subject->getOutput();

        Assert::assertEquals(
            $expected,
            $output,
            $message ?: 'Failed asserting that output equals expected'
        );
    }

    /**
     * Assert that the output is empty.
     */
    public function assertOutputEmpty(MockInstance|TestRenderer $subject, string $message = ''): void
    {
        $output = $subject instanceof MockInstance
            ? $subject->getLastOutput()
            : $subject->getOutput();

        Assert::assertEmpty(
            $output,
            $message ?: 'Failed asserting that output is empty'
        );
    }

    /**
     * Assert that the output is not empty.
     */
    public function assertOutputNotEmpty(MockInstance|TestRenderer $subject, string $message = ''): void
    {
        $output = $subject instanceof MockInstance
            ? $subject->getLastOutput()
            : $subject->getOutput();

        Assert::assertNotEmpty(
            $output,
            $message ?: 'Failed asserting that output is not empty'
        );
    }

    /**
     * Assert that the instance is running.
     */
    public function assertInstanceRunning(MockInstance $instance, string $message = ''): void
    {
        Assert::assertTrue(
            $instance->isRunning(),
            $message ?: 'Failed asserting that instance is running'
        );
    }

    /**
     * Assert that the instance is not running.
     */
    public function assertInstanceNotRunning(MockInstance $instance, string $message = ''): void
    {
        Assert::assertFalse(
            $instance->isRunning(),
            $message ?: 'Failed asserting that instance is not running'
        );
    }

    /**
     * Assert that output contains bold text (marked with **).
     */
    public function assertHasBoldText(MockInstance|TestRenderer $subject, string $text, string $message = ''): void
    {
        $this->assertOutputContains(
            $subject,
            "**{$text}**",
            $message ?: "Failed asserting that output contains bold text '{$text}'"
        );
    }

    /**
     * Assert that output contains italic text (marked with _).
     */
    public function assertHasItalicText(MockInstance|TestRenderer $subject, string $text, string $message = ''): void
    {
        $this->assertOutputContains(
            $subject,
            "_{$text}_",
            $message ?: "Failed asserting that output contains italic text '{$text}'"
        );
    }

    /**
     * Assert output has a border.
     */
    public function assertHasBorder(MockInstance|TestRenderer $subject, string $style = 'single', string $message = ''): void
    {
        $output = $subject instanceof MockInstance
            ? $subject->getLastOutput()
            : $subject->getOutput();

        $chars = match ($style) {
            'double' => ['╔', '╗', '╚', '╝'],
            'round' => ['╭', '╮', '╰', '╯'],
            'bold' => ['┏', '┓', '┗', '┛'],
            default => ['┌', '┐', '└', '┘'],
        };

        foreach ($chars as $char) {
            Assert::assertStringContainsString(
                $char,
                $output,
                $message ?: "Failed asserting that output has {$style} border"
            );
        }
    }
}
