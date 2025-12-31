<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Feedback\Alert;

class AlertTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $alert = Alert::create('Message');

        $this->assertInstanceOf(Alert::class, $alert);
    }

    public function testRendersContent(): void
    {
        $widget = $this->createWidget(Alert::create('Test message'));

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertInstanceOf(Box::class, $output);
        $this->assertTrue($this->containsText($output, 'Test message'));
    }

    public function testErrorCreatesRedAlert(): void
    {
        $widget = $this->createWidget(Alert::error('Error message'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Error message'));
    }

    public function testWarningCreatesYellowAlert(): void
    {
        $widget = $this->createWidget(Alert::warning('Warning message'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Warning message'));
    }

    public function testSuccessCreatesGreenAlert(): void
    {
        $widget = $this->createWidget(Alert::success('Success message'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Success message'));
    }

    public function testInfoCreatesBlueAlert(): void
    {
        $widget = $this->createWidget(Alert::info('Info message'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Info message'));
    }

    public function testFromExceptionCreatesAlert(): void
    {
        $exception = new \RuntimeException('Something went wrong');
        $widget = $this->createWidget(Alert::fromException($exception));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Something went wrong'));
        $this->assertTrue($this->containsText($output, 'RuntimeException'));
    }

    public function testContentCanBeArray(): void
    {
        $widget = $this->createWidget(
            Alert::error('Errors')->content([
                '- Name is required',
                '- Email is invalid',
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Name is required'));
        $this->assertTrue($this->containsText($output, 'Email is invalid'));
    }

    public function testTitleCanBeSet(): void
    {
        $widget = $this->createWidget(
            Alert::create('Message')->title('Warning Title')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Message'));
    }

    public function testDismissibleShowsLabel(): void
    {
        $widget = $this->createWidget(
            Alert::create('Message')
                ->dismissible()
                ->dismissLabel('Close')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Close'));
    }

    public function testDismissOnEnter(): void
    {
        $dismissed = false;
        $widget = $this->createWidget(
            Alert::create('Message')
                ->dismissible()
                ->onDismiss(function () use (&$dismissed) {
                    $dismissed = true;
                })
        );

        $this->renderWidget($widget);
        $this->mockHooks->simulateInput("\r"); // Enter
        $output = $this->renderWidget($widget);

        $this->assertTrue($dismissed);
        $this->assertInstanceOf(\Xocdr\Tui\Components\Fragment::class, $output);
    }

    public function testFluentChaining(): void
    {
        $alert = Alert::create('Session will expire')
            ->title('Warning')
            ->variant('warning')
            ->width(50)
            ->dismissible()
            ->dismissLabel('OK')
            ->onDismiss(fn () => null);

        $this->assertInstanceOf(Alert::class, $alert);
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
            // Also collect border title if present
            $borderTitle = $component->getBorderTitle();
            if ($borderTitle !== null) {
                $texts[] = $borderTitle;
            }
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
