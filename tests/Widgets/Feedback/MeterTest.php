<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Feedback\Meter;

class MeterTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $meter = Meter::create();

        $this->assertInstanceOf(Meter::class, $meter);
    }

    public function testRendersProgressBar(): void
    {
        $widget = $this->createWidget(Meter::create()->value(50)->max(100));

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertInstanceOf(Box::class, $output);
    }

    public function testShowsPercentageByDefault(): void
    {
        $widget = $this->createWidget(Meter::create()->value(50)->max(100));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '50%'));
    }

    public function testShowsFractionFormat(): void
    {
        $widget = $this->createWidget(
            Meter::create()
                ->value(25)
                ->max(100)
                ->valueFormat('fraction')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '25/100'));
    }

    public function testCanHideValue(): void
    {
        $widget = $this->createWidget(
            Meter::create()
                ->value(50)
                ->max(100)
                ->showValue(false)
        );

        $output = $this->renderWidget($widget);

        $this->assertFalse($this->containsText($output, '50%'));
    }

    public function testLabelCanBeSet(): void
    {
        $widget = $this->createWidget(
            Meter::create()
                ->value(75)
                ->max(100)
                ->label('Progress')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Progress'));
    }

    public function testBracketsCanBeEnabled(): void
    {
        $widget = $this->createWidget(
            Meter::create()
                ->value(50)
                ->max(100)
                ->brackets()
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '['));
        $this->assertTrue($this->containsText($output, ']'));
    }

    public function testCustomFilledChar(): void
    {
        $widget = $this->createWidget(
            Meter::create()
                ->value(50)
                ->max(100)
                ->filledChar('#')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '#'));
    }

    public function testMinMaxRange(): void
    {
        $widget = $this->createWidget(
            Meter::create()
                ->min(0)
                ->max(200)
                ->value(100)
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '50%'));
    }

    public function testFluentChaining(): void
    {
        $meter = Meter::create()
            ->value(75)
            ->min(0)
            ->max(100)
            ->width(30)
            ->label('CPU')
            ->showValue(true)
            ->valueFormat('percent')
            ->brackets()
            ->color('green');

        $this->assertInstanceOf(Meter::class, $meter);
    }

    public function testIndeterminateMode(): void
    {
        $meter = Meter::create()
            ->label('Loading')
            ->indeterminate()
            ->indeterminateChar('▓');

        $this->assertInstanceOf(Meter::class, $meter);
    }

    public function testRendersIndeterminateMode(): void
    {
        $widget = $this->createWidget(
            Meter::create()
                ->label('Processing')
                ->indeterminate()
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testShowEtaAndSpeed(): void
    {
        $meter = Meter::create()
            ->value(50)
            ->max(100)
            ->startTime(microtime(true) - 10)
            ->showEta()
            ->showSpeed()
            ->speedUnit(' items/s');

        $this->assertInstanceOf(Meter::class, $meter);
    }

    public function testShowElapsed(): void
    {
        $meter = Meter::create()
            ->value(25)
            ->max(100)
            ->startTime(microtime(true) - 60)
            ->showElapsed();

        $this->assertInstanceOf(Meter::class, $meter);
    }

    public function testRendersWithEtaAndSpeed(): void
    {
        $widget = $this->createWidget(
            Meter::create()
                ->value(50)
                ->max(100)
                ->startTime(microtime(true) - 5)
                ->showEta()
                ->showSpeed()
                ->showElapsed()
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
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
