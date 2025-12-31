<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Feedback\Toast;

class ToastTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $toast = Toast::create('Message');

        $this->assertInstanceOf(Toast::class, $toast);
    }

    public function testRendersMessage(): void
    {
        $widget = $this->createWidget(Toast::create('Test notification'));

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Test notification'));
    }

    public function testSuccessToast(): void
    {
        $widget = $this->createWidget(Toast::success('Operation complete'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Operation complete'));
        $this->assertTrue($this->containsText($output, '✓'));
    }

    public function testErrorToast(): void
    {
        $widget = $this->createWidget(Toast::error('Something failed'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Something failed'));
        $this->assertTrue($this->containsText($output, '✗'));
    }

    public function testWarningToast(): void
    {
        $widget = $this->createWidget(Toast::warning('Be careful'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Be careful'));
    }

    public function testInfoToast(): void
    {
        $widget = $this->createWidget(Toast::info('FYI'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'FYI'));
    }

    public function testTitleCanBeSet(): void
    {
        $widget = $this->createWidget(
            Toast::create('Details here')
                ->title('Important')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Important'));
        $this->assertTrue($this->containsText($output, 'Details here'));
    }

    public function testDismissibleShowsCloseButton(): void
    {
        $widget = $this->createWidget(
            Toast::create('Message')
                ->dismissible()
                ->persistent()
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '[x]'));
    }

    public function testDismissOnEscape(): void
    {
        $dismissed = false;
        $widget = $this->createWidget(
            Toast::create('Message')
                ->dismissible()
                ->persistent()
                ->onDismiss(function () use (&$dismissed) {
                    $dismissed = true;
                })
        );

        $this->renderWidget($widget);
        $this->mockHooks->simulateInput("\x1b"); // Escape
        $output = $this->renderWidget($widget);

        $this->assertTrue($dismissed);
        $this->assertInstanceOf(\Xocdr\Tui\Components\Fragment::class, $output);
    }

    public function testPersistentToastDoesNotAutoExpire(): void
    {
        $widget = $this->createWidget(
            Toast::create('Message')->persistent()
        );

        $output = $this->renderWidget($widget);

        // Should still render after multiple renders
        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Message'));
    }

    public function testCustomIcon(): void
    {
        $widget = $this->createWidget(
            Toast::create('Custom')
                ->icon('=�')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '=�'));
    }

    public function testFluentChaining(): void
    {
        $toast = Toast::create('Notification')
            ->variant('success')
            ->title('Done')
            ->duration(5000)
            ->dismissible()
            ->icon('✓')
            ->onDismiss(fn () => null)
            ->onExpire(fn () => null);

        $this->assertInstanceOf(Toast::class, $toast);
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
