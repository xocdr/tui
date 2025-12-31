<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Tests\Streaming;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Streaming\StreamingText;

class StreamingTextTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $widget = StreamingText::create();

        $this->assertInstanceOf(StreamingText::class, $widget);
    }

    public function testRendersContent(): void
    {
        $widget = $this->createWidget(
            StreamingText::create('Hello world')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Hello world'));
    }

    public function testRendersPlaceholderWhenEmpty(): void
    {
        $widget = $this->createWidget(
            StreamingText::create('')
                ->placeholder('Waiting for input...')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Waiting for input...'));
    }

    public function testShowsCursorWhenStreaming(): void
    {
        $widget = $this->createWidget(
            StreamingText::create('Streaming')
                ->streaming(true)
                ->showCursor(true)
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Streaming'));
        // Cursor is now a spinner - check for first frame of 'dots' spinner
        $this->assertTrue($this->containsText($output, '⠋'));
    }

    public function testHidesCursorWhenNotStreaming(): void
    {
        $widget = $this->createWidget(
            StreamingText::create('Complete')
                ->streaming(false)
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Complete'));
        // When not streaming, no spinner should appear
        $this->assertFalse($this->containsText($output, '⠋'));
    }

    public function testCursorCanBeHidden(): void
    {
        $widget = $this->createWidget(
            StreamingText::create('Text')
                ->streaming(true)
                ->showCursor(false)
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Text'));
    }

    public function testAppendContent(): void
    {
        $streamingText = StreamingText::create('Hello');
        $streamingText->append(' World');

        $widget = $this->createWidget($streamingText);

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Hello World'));
    }

    public function testMultilineContent(): void
    {
        $widget = $this->createWidget(
            StreamingText::create("Line 1\nLine 2\nLine 3")
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Line 1'));
        $this->assertTrue($this->containsText($output, 'Line 2'));
        $this->assertTrue($this->containsText($output, 'Line 3'));
    }

    public function testWordWrap(): void
    {
        $widget = $this->createWidget(
            StreamingText::create('This is a very long line that should be wrapped')
                ->maxWidth(20)
                ->wordWrap(true)
        );

        $output = $this->renderWidget($widget);

        $this->assertInstanceOf(Box::class, $output);
    }

    public function testFluentChaining(): void
    {
        $widget = StreamingText::create()
            ->content('Content')
            ->streaming(true)
            ->cursorChar('_')
            ->showCursor(true)
            ->cursorBlinkInterval(500)
            ->maxWidth(80)
            ->wordWrap(true)
            ->color('cyan')
            ->placeholder('Loading...');

        $this->assertInstanceOf(StreamingText::class, $widget);
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
