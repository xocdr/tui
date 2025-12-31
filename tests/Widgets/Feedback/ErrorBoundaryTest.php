<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Feedback\ErrorBoundary;

class ErrorBoundaryTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $widget = ErrorBoundary::create();

        $this->assertInstanceOf(ErrorBoundary::class, $widget);
    }

    public function testRendersChildrenWhenNoError(): void
    {
        $widget = $this->createWidget(
            ErrorBoundary::create()
                ->children(Text::create('Hello World'))
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Hello World'));
    }

    public function testRendersCallableChildren(): void
    {
        $widget = $this->createWidget(
            ErrorBoundary::create()
                ->children(fn () => Text::create('Dynamic content'))
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Dynamic content'));
    }

    public function testCatchesExceptionAndShowsDefaultFallback(): void
    {
        $widget = $this->createWidget(
            ErrorBoundary::create()
                ->children(function () {
                    throw new \RuntimeException('Something broke');
                })
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Error'));
        $this->assertTrue($this->containsText($output, 'Something broke'));
        $this->assertTrue($this->containsText($output, 'RuntimeException'));
    }

    public function testCustomFallbackRendered(): void
    {
        $widget = $this->createWidget(
            ErrorBoundary::create()
                ->children(function () {
                    throw new \Exception('Oops');
                })
                ->fallback(Text::create('Custom error message'))
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Custom error message'));
    }

    public function testCallableFallbackReceivesError(): void
    {
        $widget = $this->createWidget(
            ErrorBoundary::create()
                ->children(function () {
                    throw new \InvalidArgumentException('Bad input');
                })
                ->fallback(function (\Throwable $e) {
                    return Text::create('Caught: ' . $e->getMessage());
                })
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Caught: Bad input'));
    }

    public function testOnErrorCallbackIsCalled(): void
    {
        $caughtError = null;
        $widget = $this->createWidget(
            ErrorBoundary::create()
                ->children(function () {
                    throw new \LogicException('Test error');
                })
                ->onError(function (\Throwable $e) use (&$caughtError) {
                    $caughtError = $e;
                })
        );

        $this->renderWidget($widget);

        $this->assertNotNull($caughtError);
        $this->assertInstanceOf(\LogicException::class, $caughtError);
        $this->assertEquals('Test error', $caughtError->getMessage());
    }

    public function testFluentChaining(): void
    {
        $widget = ErrorBoundary::create()
            ->children(Text::create('Content'))
            ->fallback(Text::create('Error'))
            ->onError(fn ($e) => null);

        $this->assertInstanceOf(ErrorBoundary::class, $widget);
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
