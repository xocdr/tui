<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Text;

class BoxTest extends TestCase
{
    public function testCreate(): void
    {
        $box = new Box;

        $this->assertInstanceOf(Box::class, $box);
    }

    public function testFlexDirection(): void
    {
        $box = (new Box)->flexDirection('column');

        $this->assertEquals(['flexDirection' => 'column'], $box->getStyle());
    }

    public function testColumn(): void
    {
        $box = new BoxColumn;

        $this->assertEquals(['flexDirection' => 'column'], $box->getStyle());
    }

    public function testRow(): void
    {
        $box = new BoxRow;

        $this->assertEquals(['flexDirection' => 'row'], $box->getStyle());
    }

    public function testChildren(): void
    {
        $box = (new Box)->children([
            new Text('Hello'),
            new Text('World'),
        ]);

        $this->assertCount(2, $box->getChildren());
    }

    public function testChild(): void
    {
        $box = (new Box)
            ->child(new Text('Hello'))
            ->child(new Text('World'));

        $this->assertCount(2, $box->getChildren());
    }

    public function testPadding(): void
    {
        $box = (new Box)->padding(2);

        $this->assertEquals(['padding' => 2], $box->getStyle());
    }

    public function testBorder(): void
    {
        $box = (new Box)->border('round');

        $this->assertEquals(['borderStyle' => 'round'], $box->getStyle());
    }

    public function testRender(): void
    {
        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui extension is required for this test');
        }

        $box = (new Box)
            ->flexDirection('column')
            ->children([
                new Text('Hello'),
            ]);

        $rendered = $box->render();

        // render() now returns a TuiBox object
        $this->assertInstanceOf(\Xocdr\Tui\Ext\Box::class, $rendered);
        $this->assertEquals('column', $rendered->flexDirection);
        $this->assertCount(1, $rendered->children);
    }

    // Tests for ->styles() method

    public function testStylesWithBorder(): void
    {
        $box = (new Box)->styles('border');
        $style = $box->getStyle();

        $this->assertEquals('single', $style['borderStyle']);
    }

    public function testStylesWithBorderStyle(): void
    {
        $box = (new Box)->styles('border-round');
        $style = $box->getStyle();

        $this->assertEquals('round', $style['borderStyle']);
    }

    public function testStylesWithBorderColor(): void
    {
        $box = (new Box)->styles('border border-blue-500');
        $style = $box->getStyle();

        $this->assertEquals('single', $style['borderStyle']);
        $this->assertEquals('#3b82f6', $style['borderColor']);
    }

    public function testStylesWithBgColor(): void
    {
        $box = (new Box)->styles('bg-slate-900');
        $style = $box->getStyle();

        $this->assertEquals('#0f172a', $style['bgColor']);
    }

    public function testStylesWithPadding(): void
    {
        $box = (new Box)->styles('p-2');
        $style = $box->getStyle();

        $this->assertEquals(2, $style['padding']);
    }

    public function testStylesWithPaddingX(): void
    {
        $box = (new Box)->styles('px-3');
        $style = $box->getStyle();

        $this->assertEquals(3, $style['paddingLeft']);
        $this->assertEquals(3, $style['paddingRight']);
    }

    public function testStylesWithPaddingY(): void
    {
        $box = (new Box)->styles('py-1');
        $style = $box->getStyle();

        $this->assertEquals(1, $style['paddingTop']);
        $this->assertEquals(1, $style['paddingBottom']);
    }

    public function testStylesWithFlexCol(): void
    {
        $box = (new Box)->styles('flex-col');
        $style = $box->getStyle();

        $this->assertEquals('column', $style['flexDirection']);
    }

    public function testStylesWithFlexRow(): void
    {
        $box = (new Box)->styles('flex-row');
        $style = $box->getStyle();

        $this->assertEquals('row', $style['flexDirection']);
    }

    public function testStylesWithItemsCenter(): void
    {
        $box = (new Box)->styles('items-center');
        $style = $box->getStyle();

        $this->assertEquals('center', $style['alignItems']);
    }

    public function testStylesWithJustifyCenter(): void
    {
        $box = (new Box)->styles('justify-center');
        $style = $box->getStyle();

        $this->assertEquals('center', $style['justifyContent']);
    }

    public function testStylesWithGap(): void
    {
        $box = (new Box)->styles('gap-2');
        $style = $box->getStyle();

        $this->assertEquals(2, $style['gap']);
    }

    public function testStylesCombined(): void
    {
        $box = (new Box)->styles('border border-round border-cyan-500 bg-slate-900 p-1');
        $style = $box->getStyle();

        $this->assertEquals('round', $style['borderStyle']);
        $this->assertEquals('#06b6d4', $style['borderColor']);
        $this->assertEquals('#0f172a', $style['bgColor']);
        $this->assertEquals(1, $style['padding']);
    }

    public function testStylesWithCallable(): void
    {
        $hasBorder = true;
        $box = (new Box)->styles(fn () => $hasBorder ? 'border border-round' : '');
        $style = $box->getStyle();

        $this->assertEquals('round', $style['borderStyle']);
    }

    public function testStylesWithCustomColor(): void
    {
        \Xocdr\Tui\Styling\Style\Color::defineColor('box-test-accent', 'emerald', 500);

        $box = (new Box)->styles('border border-box-test-accent');
        $style = $box->getStyle();

        $this->assertEquals('#10b981', $style['borderColor']);
    }
}
