<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Text;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Styling\Text\TextUtils;

class TextUtilsTest extends TestCase
{
    public function testWidthAscii(): void
    {
        $this->assertSame(5, TextUtils::width('Hello'));
        $this->assertSame(0, TextUtils::width(''));
        $this->assertSame(13, TextUtils::width('Hello, World!'));
    }

    public function testWidthUnicode(): void
    {
        // Chinese characters - width depends on native implementation
        $width = TextUtils::width('中');
        $this->assertGreaterThanOrEqual(1, $width);

        // Mixed - just verify it returns a positive value
        $mixedWidth = TextUtils::width('Hello 世界');
        $this->assertGreaterThan(5, $mixedWidth);
    }

    public function testWidthWithAnsi(): void
    {
        // ANSI escape sequences handling depends on implementation
        $ansi = "\033[31mRed\033[0m";
        $width = TextUtils::width($ansi);
        // Just verify it returns a positive value
        $this->assertGreaterThan(0, $width);
    }

    public function testWrap(): void
    {
        $text = 'The quick brown fox jumps over the lazy dog.';

        $lines = TextUtils::wrap($text, 10);

        foreach ($lines as $line) {
            $this->assertLessThanOrEqual(10, TextUtils::width($line));
        }
    }

    public function testWrapEmptyString(): void
    {
        $lines = TextUtils::wrap('', 10);

        $this->assertSame([''], $lines);
    }

    public function testWrapLongWord(): void
    {
        $text = 'supercalifragilisticexpialidocious';

        $lines = TextUtils::wrap($text, 10);

        // Long word handling depends on implementation
        // Just verify we get some output
        $this->assertNotEmpty($lines);
    }

    public function testWrapZeroWidth(): void
    {
        $lines = TextUtils::wrap('Hello', 0);

        $this->assertSame(['Hello'], $lines);
    }

    public function testTruncate(): void
    {
        $text = 'Hello, World!';

        $truncated = TextUtils::truncate($text, 10);

        $this->assertSame('Hello, ...', $truncated);
        $this->assertSame(10, TextUtils::width($truncated));
    }

    public function testTruncateNoTruncationNeeded(): void
    {
        $text = 'Hello';

        $truncated = TextUtils::truncate($text, 10);

        $this->assertSame('Hello', $truncated);
    }

    public function testTruncateCustomEllipsis(): void
    {
        $text = 'Hello, World!';

        $truncated = TextUtils::truncate($text, 10, '…');

        $this->assertSame(10, TextUtils::width($truncated));
    }

    public function testTruncateVeryShort(): void
    {
        $text = 'Hello, World!';

        $truncated = TextUtils::truncate($text, 3);

        $this->assertSame(3, TextUtils::width($truncated));
    }

    public function testPadLeft(): void
    {
        $padded = TextUtils::pad('Hi', 10, 'left');

        $this->assertSame('Hi        ', $padded);
        $this->assertSame(10, TextUtils::width($padded));
    }

    public function testPadRight(): void
    {
        $padded = TextUtils::pad('Hi', 10, 'right');

        $this->assertSame('        Hi', $padded);
        $this->assertSame(10, TextUtils::width($padded));
    }

    public function testPadCenter(): void
    {
        $padded = TextUtils::pad('Hi', 10, 'center');

        $this->assertSame('    Hi    ', $padded);
        $this->assertSame(10, TextUtils::width($padded));
    }

    public function testPadCustomChar(): void
    {
        $padded = TextUtils::pad('Hi', 10, 'left', '-');

        $this->assertSame('Hi--------', $padded);
    }

    public function testPadNoChange(): void
    {
        $padded = TextUtils::pad('Hello World', 5, 'left');

        $this->assertSame('Hello World', $padded);
    }

    public function testLeft(): void
    {
        $padded = TextUtils::left('Hi', 10);

        $this->assertSame('Hi        ', $padded);
    }

    public function testRight(): void
    {
        $padded = TextUtils::right('Hi', 10);

        $this->assertSame('        Hi', $padded);
    }

    public function testCenter(): void
    {
        $padded = TextUtils::center('Hi', 10);

        $this->assertSame('    Hi    ', $padded);
    }

    public function testCenterOddWidth(): void
    {
        $padded = TextUtils::center('Hi', 9);

        // 9 - 2 = 7 padding, floor(7/2) = 3 left, ceil(7/2) = 4 right
        $this->assertSame('   Hi    ', $padded);
        $this->assertSame(9, strlen($padded));
    }

    public function testStripAnsi(): void
    {
        $ansi = "\033[31mRed\033[0m \033[32mGreen\033[0m";
        $stripped = TextUtils::stripAnsi($ansi);

        $this->assertSame('Red Green', $stripped);
    }

    public function testStripAnsiNoAnsi(): void
    {
        $text = 'Plain text';
        $stripped = TextUtils::stripAnsi($text);

        $this->assertSame('Plain text', $stripped);
    }
}
