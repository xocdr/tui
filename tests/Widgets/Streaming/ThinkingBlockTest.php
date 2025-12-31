<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Streaming;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Streaming\ThinkingBlock;

class ThinkingBlockTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $widget = ThinkingBlock::create();

        $this->assertInstanceOf(ThinkingBlock::class, $widget);
    }

    public function testRendersLabel(): void
    {
        $widget = $this->createWidget(
            ThinkingBlock::create()->label('Processing')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Processing'));
    }

    public function testRendersContent(): void
    {
        $widget = $this->createWidget(
            ThinkingBlock::create()
                ->content('Step 1: Analyzing...')
                ->collapsible(false)
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Step 1: Analyzing...'));
    }

    public function testCollapsibleToggle(): void
    {
        $widget = $this->createWidget(
            ThinkingBlock::create()
                ->content('Hidden content')
                ->collapsible(true)
                ->defaultExpanded(false)
        );

        // Initially collapsed
        $output = $this->renderWidget($widget);
        $this->assertFalse($this->containsText($output, 'Hidden content'));

        // Toggle with space
        $this->mockHooks->simulateInput(' ');
        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Hidden content'));
    }

    public function testDefaultExpandedShowsContent(): void
    {
        $widget = $this->createWidget(
            ThinkingBlock::create()
                ->content('Visible content')
                ->collapsible(true)
                ->defaultExpanded(true)
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Visible content'));
    }

    public function testShowsCheckmarkWhenNotThinking(): void
    {
        $widget = $this->createWidget(
            ThinkingBlock::create()
                ->label('Done')
                ->thinking(false)
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '✓'));
    }

    public function testAppendContent(): void
    {
        $thinkingBlock = ThinkingBlock::create('Initial');
        $thinkingBlock->append(' appended');

        $widget = $this->createWidget($thinkingBlock->collapsible(false));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Initial appended'));
    }

    public function testFluentChaining(): void
    {
        $widget = ThinkingBlock::create()
            ->content('Thinking...')
            ->thinking(true)
            ->label('Analysis')
            ->spinnerType('dots')
            ->showDuration(true)
            ->collapsible(true)
            ->defaultExpanded(false)
            ->color('cyan');

        $this->assertInstanceOf(ThinkingBlock::class, $widget);
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
