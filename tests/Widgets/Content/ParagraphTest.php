<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Content;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Content\Paragraph;
use Xocdr\Tui\Widgets\Content\TextSegment;

class ParagraphTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $para = Paragraph::create();

        $this->assertInstanceOf(Paragraph::class, $para);
    }

    public function testRendersText(): void
    {
        $widget = $this->createWidget(
            Paragraph::create('Hello, World!')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Hello, World!'));
    }

    public function testRendersMultilineText(): void
    {
        $widget = $this->createWidget(
            Paragraph::create("Line 1\nLine 2\nLine 3")
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Line 1'));
        $this->assertTrue($this->containsText($output, 'Line 2'));
        $this->assertTrue($this->containsText($output, 'Line 3'));
    }

    public function testRendersSegments(): void
    {
        $widget = $this->createWidget(
            Paragraph::create()
                ->segments([
                    new TextSegment('Hello ', 'red'),
                    new TextSegment('World', 'blue', bold: true),
                ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Hello'));
        $this->assertTrue($this->containsText($output, 'World'));
    }

    public function testTextCanBeUpdated(): void
    {
        $widget = $this->createWidget(
            Paragraph::create('Initial')->text('Updated text')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Updated text'));
    }

    public function testFluentChaining(): void
    {
        $para = Paragraph::create()
            ->text('Formatted paragraph')
            ->width(60)
            ->align('center')
            ->indent(2)
            ->firstLineIndent(4)
            ->lineHeight(1.2)
            ->wrap(true)
            ->color('white')
            ->bold();

        $this->assertInstanceOf(Paragraph::class, $para);
    }

    /**
     * Collect all text content from a component tree.
     */
    private function collectTextContent(mixed $component): array
    {
        $texts = [];

        if ($component instanceof Text) {
            $texts[] = $component->getContent();
        } elseif ($component instanceof Box) {
            foreach ($component->getChildren() as $child) {
                $texts = array_merge($texts, $this->collectTextContent($child));
            }
        }

        return $texts;
    }

    /**
     * Check if component tree contains text.
     */
    private function containsText(mixed $component, string $needle): bool
    {
        foreach ($this->collectTextContent($component) as $text) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }
        return false;
    }
}

class TextSegmentTest extends TuiTestCase
{
    public function testConstructorSetsProperties(): void
    {
        $segment = new TextSegment(
            text: 'Hello',
            color: 'red',
            bold: true,
            dim: false,
            italic: true,
            underline: false,
            link: 'https://example.com',
        );

        $this->assertEquals('Hello', $segment->text);
        $this->assertEquals('red', $segment->color);
        $this->assertTrue($segment->bold);
        $this->assertFalse($segment->dim);
        $this->assertTrue($segment->italic);
        $this->assertFalse($segment->underline);
        $this->assertEquals('https://example.com', $segment->link);
    }

    public function testFromCreatesFromString(): void
    {
        $segment = TextSegment::from('Simple text');

        $this->assertEquals('Simple text', $segment->text);
        $this->assertNull($segment->color);
        $this->assertFalse($segment->bold);
    }

    public function testFromCreatesFromArray(): void
    {
        $segment = TextSegment::from([
            'text' => 'Styled',
            'color' => 'blue',
            'bold' => true,
            'underline' => true,
        ]);

        $this->assertEquals('Styled', $segment->text);
        $this->assertEquals('blue', $segment->color);
        $this->assertTrue($segment->bold);
        $this->assertTrue($segment->underline);
    }
}
