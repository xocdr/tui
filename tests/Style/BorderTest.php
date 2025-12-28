<?php

declare(strict_types=1);

namespace Tui\Tests\Style;

use PHPUnit\Framework\TestCase;
use Tui\Style\Border;

class BorderTest extends TestCase
{
    public function testBorderConstants(): void
    {
        $this->assertEquals('single', Border::SINGLE);
        $this->assertEquals('double', Border::DOUBLE);
        $this->assertEquals('round', Border::ROUND);
        $this->assertEquals('bold', Border::BOLD);
        $this->assertEquals('singleDouble', Border::SINGLE_DOUBLE);
        $this->assertEquals('doubleSingle', Border::DOUBLE_SINGLE);
        $this->assertEquals('classic', Border::CLASSIC);
        $this->assertEquals('arrow', Border::ARROW);
    }

    public function testGetCharsSingle(): void
    {
        $chars = Border::getChars('single');

        $this->assertEquals('┌', $chars['topLeft']);
        $this->assertEquals('─', $chars['top']);
        $this->assertEquals('┐', $chars['topRight']);
        $this->assertEquals('│', $chars['left']);
        $this->assertEquals('│', $chars['right']);
        $this->assertEquals('└', $chars['bottomLeft']);
        $this->assertEquals('─', $chars['bottom']);
        $this->assertEquals('┘', $chars['bottomRight']);
    }

    public function testGetCharsDouble(): void
    {
        $chars = Border::getChars('double');

        $this->assertEquals('╔', $chars['topLeft']);
        $this->assertEquals('═', $chars['top']);
        $this->assertEquals('╗', $chars['topRight']);
        $this->assertEquals('║', $chars['left']);
        $this->assertEquals('║', $chars['right']);
        $this->assertEquals('╚', $chars['bottomLeft']);
        $this->assertEquals('═', $chars['bottom']);
        $this->assertEquals('╝', $chars['bottomRight']);
    }

    public function testGetCharsRound(): void
    {
        $chars = Border::getChars('round');

        $this->assertEquals('╭', $chars['topLeft']);
        $this->assertEquals('─', $chars['top']);
        $this->assertEquals('╮', $chars['topRight']);
        $this->assertEquals('│', $chars['left']);
        $this->assertEquals('│', $chars['right']);
        $this->assertEquals('╰', $chars['bottomLeft']);
        $this->assertEquals('─', $chars['bottom']);
        $this->assertEquals('╯', $chars['bottomRight']);
    }

    public function testGetCharsBold(): void
    {
        $chars = Border::getChars('bold');

        $this->assertEquals('┏', $chars['topLeft']);
        $this->assertEquals('━', $chars['top']);
        $this->assertEquals('┓', $chars['topRight']);
        $this->assertEquals('┃', $chars['left']);
        $this->assertEquals('┃', $chars['right']);
        $this->assertEquals('┗', $chars['bottomLeft']);
        $this->assertEquals('━', $chars['bottom']);
        $this->assertEquals('┛', $chars['bottomRight']);
    }

    public function testGetCharsClassic(): void
    {
        $chars = Border::getChars('classic');

        $this->assertEquals('+', $chars['topLeft']);
        $this->assertEquals('-', $chars['top']);
        $this->assertEquals('+', $chars['topRight']);
        $this->assertEquals('|', $chars['left']);
        $this->assertEquals('|', $chars['right']);
        $this->assertEquals('+', $chars['bottomLeft']);
        $this->assertEquals('-', $chars['bottom']);
        $this->assertEquals('+', $chars['bottomRight']);
    }

    public function testGetCharsArrow(): void
    {
        $chars = Border::getChars('arrow');

        $this->assertEquals('↘', $chars['topLeft']);
        $this->assertEquals('↓', $chars['top']);
        $this->assertEquals('↙', $chars['topRight']);
        $this->assertEquals('→', $chars['left']);
        $this->assertEquals('←', $chars['right']);
        $this->assertEquals('↗', $chars['bottomLeft']);
        $this->assertEquals('↑', $chars['bottom']);
        $this->assertEquals('↖', $chars['bottomRight']);
    }

    public function testGetCharsUnknownDefaultsToSingle(): void
    {
        $chars = Border::getChars('unknown');

        // Should fallback to single
        $this->assertEquals('┌', $chars['topLeft']);
        $this->assertEquals('─', $chars['top']);
        $this->assertEquals('┐', $chars['topRight']);
    }

    public function testGetCharsReturnsAllKeys(): void
    {
        $chars = Border::getChars('single');

        $this->assertArrayHasKey('topLeft', $chars);
        $this->assertArrayHasKey('top', $chars);
        $this->assertArrayHasKey('topRight', $chars);
        $this->assertArrayHasKey('left', $chars);
        $this->assertArrayHasKey('right', $chars);
        $this->assertArrayHasKey('bottomLeft', $chars);
        $this->assertArrayHasKey('bottom', $chars);
        $this->assertArrayHasKey('bottomRight', $chars);
    }
}
