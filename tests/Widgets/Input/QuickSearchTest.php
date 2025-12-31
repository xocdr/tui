<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Input;

use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Input\QuickSearch;

class QuickSearchTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $search = QuickSearch::create();

        $this->assertInstanceOf(QuickSearch::class, $search);
    }

    public function testRendersItems(): void
    {
        $widget = $this->createWidget(
            QuickSearch::create()
                ->items(['Apple', 'Banana', 'Cherry'])
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testFluentChaining(): void
    {
        $search = QuickSearch::create()
            ->items(['A', 'B', 'C'])
            ->placeholder('Type to search')
            ->maxVisible(5)
            ->fuzzyMatch(true)
            ->onSelect(fn ($item) => null)
            ->onChange(fn ($query) => null);

        $this->assertInstanceOf(QuickSearch::class, $search);
    }
}
