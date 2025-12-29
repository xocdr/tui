<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Drawing;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Drawing\Buffer;

class BufferTest extends TestCase
{
    /**
     * Create a buffer using PHP fallback (not native extension).
     */
    private function createBuffer(int $width, int $height): Buffer
    {
        return new Buffer($width, $height, useNative: false);
    }

    public function testCreate(): void
    {
        $buffer = $this->createBuffer(80, 24);

        $this->assertSame(80, $buffer->getWidth());
        $this->assertSame(24, $buffer->getHeight());
    }

    public function testClear(): void
    {
        $buffer = $this->createBuffer(10, 5);
        $buffer->line(0, 0, 9, 4);
        $buffer->clear();

        $lines = $buffer->render();
        $this->assertCount(5, $lines);

        // After clear, all lines should be spaces
        foreach ($lines as $line) {
            $this->assertSame(str_repeat(' ', 10), $line);
        }
    }

    public function testLine(): void
    {
        $buffer = $this->createBuffer(10, 5);
        $buffer->line(0, 0, 9, 0);

        $lines = $buffer->render();
        $this->assertNotSame(str_repeat(' ', 10), $lines[0]);
    }

    public function testRect(): void
    {
        $buffer = $this->createBuffer(10, 5);
        $buffer->rect(0, 0, 10, 5);

        $lines = $buffer->render();

        // Top-left corner should be filled
        $this->assertStringStartsWith('█', $lines[0]);
    }

    public function testFillRect(): void
    {
        $buffer = $this->createBuffer(10, 5);
        $buffer->fillRect(2, 1, 4, 2);

        $lines = $buffer->render();

        // Check the fill area
        $this->assertStringContainsString('████', $lines[1]);
    }

    public function testCircle(): void
    {
        $buffer = $this->createBuffer(21, 11);
        $buffer->circle(10, 5, 5);

        $lines = $buffer->render();

        // Circle should have some filled pixels
        $hasContent = false;
        foreach ($lines as $line) {
            if (str_contains($line, '█')) {
                $hasContent = true;
                break;
            }
        }
        $this->assertTrue($hasContent);
    }

    public function testTriangle(): void
    {
        $buffer = $this->createBuffer(20, 10);
        $buffer->triangle(10, 0, 0, 9, 19, 9);

        $lines = $buffer->render();

        // Should have triangle drawn
        $hasContent = false;
        foreach ($lines as $line) {
            if (str_contains($line, '█')) {
                $hasContent = true;
                break;
            }
        }
        $this->assertTrue($hasContent);
    }

    public function testSetCell(): void
    {
        $buffer = $this->createBuffer(10, 5);
        $buffer->setCell(5, 2, 'X');

        $lines = $buffer->render();
        $this->assertSame('X', mb_substr($lines[2], 5, 1));
    }

    public function testSetCellOutOfBounds(): void
    {
        $buffer = $this->createBuffer(10, 5);

        // These should not throw
        $buffer->setCell(-1, 0, 'X');
        $buffer->setCell(0, -1, 'X');
        $buffer->setCell(10, 0, 'X');
        $buffer->setCell(0, 5, 'X');

        $this->assertTrue(true); // No exception
    }

    public function testFluentInterface(): void
    {
        $buffer = $this->createBuffer(20, 10);

        $result = $buffer
            ->line(0, 0, 19, 9)
            ->rect(2, 2, 5, 5)
            ->circle(15, 5, 3);

        $this->assertSame($buffer, $result);
    }

    public function testRenderReturnsCorrectLineCount(): void
    {
        $buffer = $this->createBuffer(40, 15);
        $lines = $buffer->render();

        $this->assertCount(15, $lines);
    }
}
