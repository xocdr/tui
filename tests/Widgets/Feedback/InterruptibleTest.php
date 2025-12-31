<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Feedback\Interruptible;

class InterruptibleTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $widget = Interruptible::create();

        $this->assertInstanceOf(Interruptible::class, $widget);
    }

    public function testRendersChildren(): void
    {
        $widget = $this->createWidget(
            Interruptible::create()->children('Processing data...')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Processing data...'));
    }

    public function testShowsInterruptHint(): void
    {
        $widget = $this->createWidget(
            Interruptible::create()
                ->children('Working...')
                ->showHint(true)
                ->interruptLabel('Press Escape to cancel')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Press Escape to cancel'));
    }

    public function testHintCanBeHidden(): void
    {
        $widget = $this->createWidget(
            Interruptible::create()
                ->children('Working...')
                ->showHint(false)
        );

        $output = $this->renderWidget($widget);

        $this->assertFalse($this->containsText($output, 'Escape'));
    }

    public function testInterruptOnEscape(): void
    {
        $interrupted = false;
        $widget = $this->createWidget(
            Interruptible::create()
                ->children('Working...')
                ->interruptKey('escape')
                ->onInterrupt(function () use (&$interrupted) {
                    $interrupted = true;
                })
        );

        $this->renderWidget($widget);
        $this->mockHooks->simulateInput("\x1b"); // Escape
        $output = $this->renderWidget($widget);

        $this->assertTrue($interrupted);
        $this->assertTrue($this->containsText($output, 'Interrupted'));
    }

    public function testInterruptOnCustomKey(): void
    {
        $interrupted = false;
        $widget = $this->createWidget(
            Interruptible::create()
                ->children('Working...')
                ->interruptKey('q')
                ->onInterrupt(function () use (&$interrupted) {
                    $interrupted = true;
                })
        );

        $this->renderWidget($widget);
        $this->mockHooks->simulateInput('q');
        $output = $this->renderWidget($widget);

        $this->assertTrue($interrupted);
    }

    public function testNonInterruptibleDoesNotRespond(): void
    {
        $interrupted = false;
        $widget = $this->createWidget(
            Interruptible::create()
                ->children('Working...')
                ->interruptible(false)
                ->onInterrupt(function () use (&$interrupted) {
                    $interrupted = true;
                })
        );

        $this->renderWidget($widget);
        $this->mockHooks->simulateInput("\x1b"); // Escape
        $output = $this->renderWidget($widget);

        $this->assertFalse($interrupted);
        $this->assertTrue($this->containsText($output, 'Working...'));
    }

    public function testFluentChaining(): void
    {
        $widget = Interruptible::create()
            ->children('Content')
            ->interruptKey('escape')
            ->interruptLabel('Press ESC to stop')
            ->showHint(true)
            ->interruptible(true)
            ->onInterrupt(fn () => null)
            ->onComplete(fn () => null);

        $this->assertInstanceOf(Interruptible::class, $widget);
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
