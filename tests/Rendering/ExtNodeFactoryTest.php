<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Rendering;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Contracts\ExtNodeFactoryInterface;
use Xocdr\Tui\Tests\Mocks\MockExtBox;
use Xocdr\Tui\Tests\Mocks\MockExtNewline;
use Xocdr\Tui\Tests\Mocks\MockExtNodeFactory;
use Xocdr\Tui\Tests\Mocks\MockExtText;

class ExtNodeFactoryTest extends TestCase
{
    private MockExtNodeFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new MockExtNodeFactory();
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(ExtNodeFactoryInterface::class, $this->factory);
    }

    public function testCreateBoxReturnsBoxObject(): void
    {
        $box = $this->factory->createBox(['padding' => 1]);

        $this->assertInstanceOf(MockExtBox::class, $box);
        $this->assertSame(['padding' => 1], $box->style);
    }

    public function testCreateBoxWithEmptyStyle(): void
    {
        $box = $this->factory->createBox();

        $this->assertInstanceOf(MockExtBox::class, $box);
        $this->assertSame([], $box->style);
    }

    public function testCreateTextReturnsTextObject(): void
    {
        $text = $this->factory->createText('Hello', ['bold' => true]);

        $this->assertInstanceOf(MockExtText::class, $text);
        $this->assertSame('Hello', $text->content);
        $this->assertSame(['bold' => true], $text->style);
    }

    public function testCreateTextWithEmptyStyle(): void
    {
        $text = $this->factory->createText('World');

        $this->assertInstanceOf(MockExtText::class, $text);
        $this->assertSame('World', $text->content);
        $this->assertSame([], $text->style);
    }

    public function testCreateNewlineReturnsNewlineObject(): void
    {
        $newline = $this->factory->createNewline(3);

        $this->assertInstanceOf(MockExtNewline::class, $newline);
        $this->assertSame(3, $newline->count);
    }

    public function testCreateNewlineWithDefaultCount(): void
    {
        $newline = $this->factory->createNewline();

        $this->assertInstanceOf(MockExtNewline::class, $newline);
        $this->assertSame(1, $newline->count);
    }

    public function testCreateSpacerReturnsFlexGrowBox(): void
    {
        $spacer = $this->factory->createSpacer();

        $this->assertInstanceOf(MockExtBox::class, $spacer);
        $this->assertSame(['flexGrow' => 1], $spacer->style);
    }

    public function testTracksCreatedNodes(): void
    {
        $this->factory->createBox(['padding' => 1]);
        $this->factory->createText('Hello', ['bold' => true]);
        $this->factory->createNewline(2);
        $this->factory->createSpacer();

        $this->assertCount(4, $this->factory->createdNodes);
        $this->assertSame('box', $this->factory->createdNodes[0]['type']);
        $this->assertSame('text', $this->factory->createdNodes[1]['type']);
        $this->assertSame('newline', $this->factory->createdNodes[2]['type']);
        $this->assertSame('spacer', $this->factory->createdNodes[3]['type']);
    }

    public function testResetClearsTrackedNodes(): void
    {
        $this->factory->createBox();
        $this->factory->createText('Test');
        $this->assertCount(2, $this->factory->createdNodes);

        $this->factory->reset();
        $this->assertCount(0, $this->factory->createdNodes);
    }

    public function testBoxCanHaveChildren(): void
    {
        $box = $this->factory->createBox();
        $text = $this->factory->createText('Child');

        $box->addChild($text);

        $this->assertCount(1, $box->getChildren());
        $this->assertSame($text, $box->getChildren()[0]);
    }
}
