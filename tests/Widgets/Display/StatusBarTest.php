<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Display;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Feedback\Segments\CallbackSegment;
use Xocdr\Tui\Widgets\Feedback\Segments\GitSegment;
use Xocdr\Tui\Widgets\Feedback\Segments\MeterSegment;
use Xocdr\Tui\Widgets\Feedback\Segments\TextSegment;
use Xocdr\Tui\Widgets\Feedback\Segments\TimerSegment;
use Xocdr\Tui\Widgets\Feedback\StatusBar;
use Xocdr\Tui\Widgets\Feedback\StatusBarContext;

class StatusBarTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $bar = StatusBar::create();

        $this->assertInstanceOf(StatusBar::class, $bar);
    }

    public function testRendersLeftSegments(): void
    {
        $widget = $this->createWidget(
            StatusBar::create()->left([
                TextSegment::create('Left Text'),
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Left Text'));
    }

    public function testRendersRightSegments(): void
    {
        $widget = $this->createWidget(
            StatusBar::create()->right([
                TextSegment::create('Right Text'),
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Right Text'));
    }

    public function testRendersMultipleSegments(): void
    {
        $widget = $this->createWidget(
            StatusBar::create()
                ->left([
                    TextSegment::create('[Opus]'),
                ])
                ->right([
                    TextSegment::create('main'),
                ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '[Opus]'));
        $this->assertTrue($this->containsText($output, 'main'));
    }

    public function testSegmentCanBeAddedById(): void
    {
        $bar = StatusBar::create()
            ->segment('model', TextSegment::create('Opus'));

        $this->assertInstanceOf(StatusBar::class, $bar);
    }

    public function testSegmentCanBeRemoved(): void
    {
        $bar = StatusBar::create()
            ->segment('model', TextSegment::create('Opus'))
            ->removeSegment('model');

        $this->assertInstanceOf(StatusBar::class, $bar);
    }

    public function testFluentChaining(): void
    {
        $bar = StatusBar::create()
            ->left([
                TextSegment::create('[Opus]'),
                GitSegment::create(),
            ])
            ->right([
                MeterSegment::create()->current(50)->max(100),
                TimerSegment::create(),
            ])
            ->separator('  ')
            ->backgroundColor('blue')
            ->contextProvider(fn () => ['git' => ['branch' => 'main']]);

        $this->assertInstanceOf(StatusBar::class, $bar);
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

class StatusBarContextTest extends TuiTestCase
{
    public function testConstructWithDefaults(): void
    {
        $context = new StatusBarContext();

        $this->assertEquals([], $context->data);
        $this->assertEquals(80, $context->terminalWidth);
        $this->assertIsFloat($context->timestamp);
    }

    public function testConstructWithData(): void
    {
        $context = new StatusBarContext(
            data: ['key' => 'value'],
            terminalWidth: 120,
            timestamp: 1234567890.0
        );

        $this->assertEquals(['key' => 'value'], $context->data);
        $this->assertEquals(120, $context->terminalWidth);
        $this->assertEquals(1234567890.0, $context->timestamp);
    }
}

class TextSegmentTest extends TuiTestCase
{
    public function testCreateWithString(): void
    {
        $segment = TextSegment::create('Static text');

        $this->assertInstanceOf(TextSegment::class, $segment);
    }

    public function testCreateWithCallable(): void
    {
        $segment = TextSegment::create(fn ($ctx) => 'Dynamic');

        $this->assertInstanceOf(TextSegment::class, $segment);
    }

    public function testIsVisibleReturnsTrue(): void
    {
        $segment = TextSegment::create('Text');
        $context = new StatusBarContext();

        $this->assertTrue($segment->isVisible($context));
    }

    public function testIsVisibleRespectsCallback(): void
    {
        $segment = TextSegment::create('Text')
            ->visibleWhen(fn ($ctx) => false);
        $context = new StatusBarContext();

        $this->assertFalse($segment->isVisible($context));
    }
}

class MeterSegmentTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $segment = MeterSegment::create();

        $this->assertInstanceOf(MeterSegment::class, $segment);
    }

    public function testCurrentCanBeInt(): void
    {
        $segment = MeterSegment::create()->current(50);

        $this->assertInstanceOf(MeterSegment::class, $segment);
    }

    public function testCurrentCanBeCallable(): void
    {
        $segment = MeterSegment::create()->current(fn ($ctx) => 50);

        $this->assertInstanceOf(MeterSegment::class, $segment);
    }
}

class GitSegmentTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $segment = GitSegment::create();

        $this->assertInstanceOf(GitSegment::class, $segment);
    }

    public function testBranchProviderCanBeSet(): void
    {
        $segment = GitSegment::create()
            ->branchProvider(fn ($ctx) => 'main');

        $this->assertInstanceOf(GitSegment::class, $segment);
    }
}

class TimerSegmentTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $segment = TimerSegment::create();

        $this->assertInstanceOf(TimerSegment::class, $segment);
    }

    public function testSinceCanBeSet(): void
    {
        $segment = TimerSegment::create()->since(microtime(true));

        $this->assertInstanceOf(TimerSegment::class, $segment);
    }
}

class CallbackSegmentTest extends TuiTestCase
{
    public function testCreateWithCallback(): void
    {
        $segment = CallbackSegment::create(fn ($ctx) => 'Custom');

        $this->assertInstanceOf(CallbackSegment::class, $segment);
    }

    public function testVisibleWhenCanBeSet(): void
    {
        $segment = CallbackSegment::create(fn ($ctx) => 'Custom')
            ->visibleWhen(fn ($ctx) => true);

        $this->assertInstanceOf(CallbackSegment::class, $segment);
    }
}
