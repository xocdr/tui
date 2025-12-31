<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Support;

use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Support\FuzzyMatch;
use Xocdr\Tui\Widgets\Support\FuzzyMatcher;

class FuzzyMatcherTest extends TuiTestCase
{
    public function testMatchReturnsEmptyForNoMatch(): void
    {
        $matcher = new FuzzyMatcher();
        $results = $matcher->match('xyz', ['hello', 'world']);

        $this->assertEmpty($results);
    }

    public function testMatchReturnsFuzzyMatchForMatch(): void
    {
        $matcher = new FuzzyMatcher();
        $results = $matcher->match('hlo', ['hello']);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(FuzzyMatch::class, $results[0]);
    }

    public function testMatchScoresExactMatchHigher(): void
    {
        $matcher = new FuzzyMatcher();

        $exactResults = $matcher->match('hello', ['hello']);
        $partialResults = $matcher->match('hlo', ['hello']);

        $this->assertNotEmpty($exactResults);
        $this->assertNotEmpty($partialResults);
        $this->assertGreaterThan($partialResults[0]->score, $exactResults[0]->score);
    }

    public function testSearchReturnsMatchingItems(): void
    {
        $matcher = new FuzzyMatcher();
        $items = ['apple', 'banana', 'apricot', 'orange'];

        $results = $matcher->search('ap', $items);

        $this->assertCount(2, $results);
        $this->assertEquals('apple', $results[0]->text);
        $this->assertEquals('apricot', $results[1]->text);
    }

    public function testSearchWithCustomAccessor(): void
    {
        $matcher = new FuzzyMatcher();
        $items = [
            ['name' => 'apple', 'price' => 1.00],
            ['name' => 'banana', 'price' => 0.50],
            ['name' => 'apricot', 'price' => 1.50],
        ];

        $results = $matcher->search('ap', $items, fn ($item) => $item['name']);

        $this->assertCount(2, $results);
    }

    public function testSearchWithLimit(): void
    {
        $matcher = new FuzzyMatcher();
        $items = ['apple', 'apricot', 'application', 'approach'];

        $results = $matcher->search('ap', $items, null, 2);

        $this->assertCount(2, $results);
    }

    public function testCaseInsensitiveMatchByDefault(): void
    {
        $matcher = new FuzzyMatcher();
        $results = $matcher->match('HELLO', ['hello']);

        $this->assertNotEmpty($results);
    }

    public function testCaseSensitiveMatch(): void
    {
        $matcher = FuzzyMatcher::create()->caseSensitive(true);
        $results = $matcher->match('HELLO', ['hello']);

        $this->assertEmpty($results);
    }

    public function testMatchReturnsPositions(): void
    {
        $matcher = new FuzzyMatcher();
        $results = $matcher->match('hlo', ['hello']);

        $this->assertNotEmpty($results);
        $this->assertIsArray($results[0]->positions);
        $this->assertContains(0, $results[0]->positions); // 'h'
        $this->assertContains(2, $results[0]->positions); // 'l'
        $this->assertContains(4, $results[0]->positions); // 'o'
    }
}

class FuzzyMatchTest extends TuiTestCase
{
    public function testConstructorSetsProperties(): void
    {
        $match = new FuzzyMatch(
            text: 'hello',
            score: 1.0,
            positions: [0, 1, 2, 3, 4],
            index: 0
        );

        $this->assertEquals('hello', $match->text);
        $this->assertEquals(1.0, $match->score);
        $this->assertEquals([0, 1, 2, 3, 4], $match->positions);
    }

    public function testHighlightReturnsFormattedString(): void
    {
        $match = new FuzzyMatch(
            text: 'hello',
            score: 1.0,
            positions: [0, 2, 4],
            index: 0
        );

        $highlighted = $match->highlight('<', '>');

        $this->assertStringContainsString('<h>', $highlighted);
        $this->assertStringContainsString('<l>', $highlighted);
        $this->assertStringContainsString('<o>', $highlighted);
    }
}
