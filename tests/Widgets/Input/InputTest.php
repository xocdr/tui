<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Input;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Input\Input;

class InputTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $input = Input::create();

        $this->assertInstanceOf(Input::class, $input);
    }

    public function testRendersPlaceholder(): void
    {
        $widget = $this->createWidget(
            Input::create()->placeholder('Type here...')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Type here...'));
    }

    public function testRendersValue(): void
    {
        $widget = $this->createWidget(
            Input::create()->value('Hello World')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Hello World'));
    }

    public function testRendersPrompt(): void
    {
        $widget = $this->createWidget(
            Input::create()->prompt('>>> ')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '>>>'));
    }

    public function testRendersHint(): void
    {
        $widget = $this->createWidget(
            Input::create()->hint('Press Enter to submit')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Press Enter to submit'));
    }

    public function testMaskedHidesValue(): void
    {
        $widget = $this->createWidget(
            Input::create()
                ->value('secret')
                ->masked()
                ->maskChar('*')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '******'));
        $this->assertFalse($this->containsText($output, 'secret'));
    }

    public function testOnSubmitCallback(): void
    {
        $submittedValue = null;
        $widget = $this->createWidget(
            Input::create()
                ->value('test input')
                ->isFocused(true)
                ->onSubmit(function ($value) use (&$submittedValue) {
                    $submittedValue = $value;
                })
        );

        $this->renderWidget($widget);

        // Simulate Enter key
        $this->mockHooks->simulateInput("\r");
        $this->renderWidget($widget);

        $this->assertEquals('test input', $submittedValue);
    }

    public function testOnCancelCallback(): void
    {
        $cancelled = false;
        $widget = $this->createWidget(
            Input::create()
                ->isFocused(true)
                ->onCancel(function () use (&$cancelled) {
                    $cancelled = true;
                })
        );

        $this->renderWidget($widget);

        // Simulate Escape key
        $this->mockHooks->simulateInput("\x1b");
        $this->renderWidget($widget);

        $this->assertTrue($cancelled);
    }

    public function testHistoryNavigation(): void
    {
        $widget = $this->createWidget(
            Input::create()
                ->history(['command1', 'command2', 'command3'])
                ->isFocused(true)
        );

        $this->renderWidget($widget);

        // Navigate up through history
        $this->mockHooks->simulateInput("\x1b[A"); // Up arrow
        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testFluentChaining(): void
    {
        $input = Input::create()
            ->value('initial')
            ->placeholder('Type here')
            ->prompt('> ')
            ->hint('Enter to submit')
            ->isFocused(true)
            ->cursorStyle('block')
            ->cursorBlink(true)
            ->blinkRate(530)
            ->history(['a', 'b'])
            ->onSubmit(fn ($v) => null)
            ->onChange(fn ($v) => null);

        $this->assertInstanceOf(Input::class, $input);
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
