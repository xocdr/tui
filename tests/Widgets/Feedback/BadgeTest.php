<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Feedback\Badge;
use Xocdr\Tui\Widgets\Support\Icon;

class BadgeTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $badge = Badge::create('Status');

        $this->assertInstanceOf(Badge::class, $badge);
    }

    public function testRendersText(): void
    {
        $widget = $this->createWidget(Badge::create('Status'));

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Status'));
    }

    public function testSuccessCreatesGreenBadge(): void
    {
        $widget = $this->createWidget(Badge::success('Build passed'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Build passed'));
        $this->assertTrue($this->containsText($output, '✓'));
    }

    public function testErrorCreatesRedBadge(): void
    {
        $widget = $this->createWidget(Badge::error('Tests failed'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Tests failed'));
        $this->assertTrue($this->containsText($output, '✗'));
    }

    public function testWarningCreatesYellowBadge(): void
    {
        $widget = $this->createWidget(Badge::warning('3 warnings'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '3 warnings'));
    }

    public function testInfoCreatesBlueBadge(): void
    {
        $widget = $this->createWidget(Badge::info('12 files'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '12 files'));
    }

    public function testLoadingCreatesAnimatedBadge(): void
    {
        $widget = $this->createWidget(Badge::loading('Processing...'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Processing...'));
    }

    public function testTextCanBeSet(): void
    {
        $widget = $this->createWidget(Badge::create()->text('Custom text'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Custom text'));
    }

    public function testDescriptionCanBeSet(): void
    {
        $widget = $this->createWidget(
            Badge::error('Failed')->description('3 tests did not pass')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Failed'));
        $this->assertTrue($this->containsText($output, '3 tests did not pass'));
    }

    public function testDescriptionHiddenInCompactMode(): void
    {
        $widget = $this->createWidget(
            Badge::error('Failed')
                ->description('3 tests did not pass')
                ->compact()
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Failed'));
        $this->assertFalse($this->containsText($output, '3 tests did not pass'));
    }

    public function testVariantCanBeSet(): void
    {
        $badge = Badge::create('Status')->variant('success');

        $this->assertInstanceOf(Badge::class, $badge);
    }

    public function testColorCanBeSet(): void
    {
        $badge = Badge::create('Custom')->color('magenta');

        $this->assertInstanceOf(Badge::class, $badge);
    }

    public function testBgColorCanBeSet(): void
    {
        $badge = Badge::create('Custom')->bgColor('green');

        $this->assertInstanceOf(Badge::class, $badge);
    }

    public function testBorderedCanBeEnabled(): void
    {
        $widget = $this->createWidget(Badge::create('Pill')->bordered());

        $output = $this->renderWidget($widget);

        $this->assertInstanceOf(Box::class, $output);
    }

    public function testIconCanBeString(): void
    {
        $widget = $this->createWidget(Badge::create('Custom')->icon('🚀'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '🚀'));
        $this->assertTrue($this->containsText($output, 'Custom'));
    }

    public function testIconCanBeIconInstance(): void
    {
        $badge = Badge::create('Custom')->icon(Icon::success());

        $this->assertInstanceOf(Badge::class, $badge);
    }

    public function testCompactCanBeEnabled(): void
    {
        $badge = Badge::create('Compact')->compact();

        $this->assertInstanceOf(Badge::class, $badge);
    }

    public function testFluentChaining(): void
    {
        $badge = Badge::create('PASS')
            ->variant('success')
            ->bgColor('green')
            ->color('white')
            ->bordered()
            ->compact();

        $this->assertInstanceOf(Badge::class, $badge);
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
