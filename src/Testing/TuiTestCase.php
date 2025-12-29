<?php

declare(strict_types=1);

namespace Xocdr\Tui\Testing;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Component;

/**
 * Base test case for TUI component testing.
 *
 * Provides a convenient API for rendering components and making assertions
 * in PHPUnit tests. Uses MockInstance for testing without the C extension.
 *
 * @example
 * class MyComponentTest extends TuiTestCase
 * {
 *     public function testRendersCorrectly(): void
 *     {
 *         $this->render(fn() => Box::column([
 *             Text::create('Hello World'),
 *         ]));
 *
 *         $this->assertTextPresent('Hello World');
 *     }
 * }
 */
abstract class TuiTestCase extends TestCase
{
    use TuiAssertions;

    protected ?MockInstance $instance = null;

    protected ?TestRenderer $renderer = null;

    protected int $defaultWidth = 80;

    protected int $defaultHeight = 24;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new TestRenderer($this->defaultWidth, $this->defaultHeight);
    }

    protected function tearDown(): void
    {
        if ($this->instance !== null) {
            $this->instance->unmount();
            $this->instance = null;
        }

        $this->renderer = null;
        parent::tearDown();
    }

    /**
     * Render a component for testing.
     *
     * @param callable|Component $component The component to render
     * @param array<string, mixed> $options Render options
     */
    protected function render(callable|Component $component, array $options = []): MockInstance
    {
        $options = array_merge([
            'width' => $this->defaultWidth,
            'height' => $this->defaultHeight,
        ], $options);

        $this->instance = new MockInstance($component, $options);
        $this->instance->start();

        return $this->instance;
    }

    /**
     * Get the current mock instance.
     */
    protected function getInstance(): ?MockInstance
    {
        return $this->instance;
    }

    /**
     * Get the test renderer.
     */
    protected function getRenderer(): ?TestRenderer
    {
        return $this->renderer;
    }

    /**
     * Render a component directly to string (without instance lifecycle).
     *
     * @param callable|Component $component The component to render
     */
    protected function renderToString(callable|Component $component): string
    {
        return $this->renderer->render($component);
    }

    /**
     * Trigger a re-render of the current component.
     */
    protected function rerender(): self
    {
        $this->instance?->rerender();

        return $this;
    }

    /**
     * Simulate keyboard input.
     *
     * @param string $key The key to simulate
     * @param array<string> $modifiers Modifiers like 'ctrl', 'shift', 'meta'
     */
    protected function pressKey(string $key, array $modifiers = []): self
    {
        $this->instance?->simulateInput($key, $modifiers);

        return $this;
    }

    /**
     * Type a sequence of characters.
     */
    protected function type(string $text): self
    {
        foreach (mb_str_split($text) as $char) {
            $this->pressKey($char);
        }

        return $this;
    }

    /**
     * Press Enter key.
     */
    protected function pressEnter(): self
    {
        return $this->pressKey("\r");
    }

    /**
     * Press Escape key.
     */
    protected function pressEscape(): self
    {
        return $this->pressKey("\x1b");
    }

    /**
     * Press Tab key.
     */
    protected function pressTab(): self
    {
        return $this->pressKey("\t");
    }

    /**
     * Press an arrow key.
     *
     * @param string $direction 'up', 'down', 'left', or 'right'
     */
    protected function pressArrow(string $direction): self
    {
        $sequences = [
            'up' => "\x1b[A",
            'down' => "\x1b[B",
            'right' => "\x1b[C",
            'left' => "\x1b[D",
        ];

        if (isset($sequences[$direction])) {
            $this->pressKey($sequences[$direction]);
        }

        return $this;
    }

    /**
     * Simulate the passage of time for timers.
     *
     * @param int $ms Milliseconds to advance
     */
    protected function advanceTimers(int $ms): self
    {
        $this->instance?->tickTimers($ms);

        return $this;
    }

    /**
     * Simulate a terminal resize.
     */
    protected function resize(int $width, int $height): self
    {
        $this->instance?->simulateResize($width, $height);

        return $this;
    }

    /**
     * Get the current rendered output.
     */
    protected function getOutput(): string
    {
        return $this->instance?->getLastOutput() ?? '';
    }

    /**
     * Get the output as an array of lines.
     *
     * @return array<string>
     */
    protected function getOutputLines(): array
    {
        return $this->instance?->getOutputLines() ?? [];
    }

    /**
     * Assert that text is present in the output.
     */
    protected function assertTextPresent(string $text, string $message = ''): void
    {
        $this->assertOutputContains(
            $this->instance ?? $this->renderer,
            $text,
            $message
        );
    }

    /**
     * Assert that text is not present in the output.
     */
    protected function assertTextNotPresent(string $text, string $message = ''): void
    {
        $this->assertOutputNotContains(
            $this->instance ?? $this->renderer,
            $text,
            $message
        );
    }

    /**
     * Assert output matches a snapshot.
     */
    protected function assertMatchesSnapshot(string $name): void
    {
        $snapshot = new Snapshot($this, $name);
        $snapshot->assertMatches($this->getOutput());
    }

    /**
     * Assert that the instance is running.
     */
    protected function assertRunning(string $message = ''): void
    {
        if ($this->instance === null) {
            $this->fail('No instance available');
        }

        $this->assertInstanceRunning($this->instance, $message);
    }

    /**
     * Assert that the instance is not running.
     */
    protected function assertNotRunning(string $message = ''): void
    {
        if ($this->instance === null) {
            $this->fail('No instance available');
        }

        $this->assertInstanceNotRunning($this->instance, $message);
    }
}
