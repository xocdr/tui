<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Input;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Widgets\Input\Autocomplete;
use Xocdr\Tui\Widgets\Input\AutocompleteSuggestion;
use Xocdr\Tui\Widgets\Input\AutocompleteTrigger;

class AutocompleteTest extends TestCase
{
    public function testCreateReturnsInstance(): void
    {
        $autocomplete = Autocomplete::create();

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testTriggerAddsPattern(): void
    {
        $autocomplete = Autocomplete::create()
            ->trigger('@');

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testTriggersAcceptsArray(): void
    {
        $autocomplete = Autocomplete::create()
            ->triggers(['@', '#', '/']);

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testSuggestionsCanBeSet(): void
    {
        $autocomplete = Autocomplete::create()
            ->suggestions([
                ['display' => 'Option 1', 'value' => 'opt1'],
                ['display' => 'Option 2', 'value' => 'opt2'],
            ]);

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testSuggestionsAcceptsSuggestionObjects(): void
    {
        $autocomplete = Autocomplete::create()
            ->suggestions([
                new AutocompleteSuggestion('Option 1', 'opt1'),
                new AutocompleteSuggestion('Option 2', 'opt2'),
            ]);

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testFuzzyCanBeEnabled(): void
    {
        $autocomplete = Autocomplete::create()->fuzzy();

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testWidthCanBeSet(): void
    {
        $autocomplete = Autocomplete::create()->width(40);

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testWidthCanBeAutoWithLimit(): void
    {
        $autocomplete = Autocomplete::create()->width('auto:50');

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testMaxVisibleCanBeSet(): void
    {
        $autocomplete = Autocomplete::create()->maxVisible(10);

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testOnSelectCallbackCanBeSet(): void
    {
        $autocomplete = Autocomplete::create()
            ->onSelect(fn ($suggestion) => null);

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testOnCancelCallbackCanBeSet(): void
    {
        $autocomplete = Autocomplete::create()
            ->onCancel(fn () => null);

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testOnTriggerCallbackCanBeSet(): void
    {
        $autocomplete = Autocomplete::create()
            ->onTrigger(fn ($trigger, $query) => null);

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testFilterCallbackCanBeSet(): void
    {
        $autocomplete = Autocomplete::create()
            ->filter(fn ($suggestions, $query) => $suggestions);

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testOpenSetsIsOpen(): void
    {
        $autocomplete = Autocomplete::create()->open('test');

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testCloseResetsState(): void
    {
        $autocomplete = Autocomplete::create()->open('test')->close();

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }

    public function testFluentChaining(): void
    {
        $autocomplete = Autocomplete::create()
            ->trigger('@')
            ->suggestions([
                ['display' => 'User 1', 'value' => 'user1'],
                ['display' => 'User 2', 'value' => 'user2'],
            ])
            ->fuzzy()
            ->width(40)
            ->maxVisible(5)
            ->onSelect(fn ($s) => null)
            ->onCancel(fn () => null);

        $this->assertInstanceOf(Autocomplete::class, $autocomplete);
    }
}

class AutocompleteSuggestionTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $suggestion = new AutocompleteSuggestion(
            display: 'Display Text',
            value: 'val',
            description: 'A description',
            icon: '📁',
        );

        $this->assertEquals('Display Text', $suggestion->display);
        $this->assertEquals('val', $suggestion->value);
        $this->assertEquals('A description', $suggestion->description);
        $this->assertEquals('📁', $suggestion->icon);
    }

    public function testFromCreatesFromArray(): void
    {
        $suggestion = AutocompleteSuggestion::from([
            'display' => 'Test',
            'value' => 'test_val',
            'description' => 'Test desc',
        ]);

        $this->assertEquals('Test', $suggestion->display);
        $this->assertEquals('test_val', $suggestion->value);
        $this->assertEquals('Test desc', $suggestion->description);
    }

    public function testFromCreatesFromString(): void
    {
        $suggestion = AutocompleteSuggestion::from('Simple');

        $this->assertEquals('Simple', $suggestion->display);
        $this->assertEquals('Simple', $suggestion->value);
    }
}

class AutocompleteTriggerTest extends TestCase
{
    public function testConstructorSetsPattern(): void
    {
        $trigger = new AutocompleteTrigger('@');

        $this->assertEquals('@', $trigger->pattern);
    }

    public function testFromCreatesFromString(): void
    {
        $trigger = AutocompleteTrigger::from('@mentions');

        $this->assertEquals('@mentions', $trigger->pattern);
    }

    public function testFromCreatesFromArray(): void
    {
        $trigger = AutocompleteTrigger::from(['pattern' => '#tags']);

        $this->assertEquals('#tags', $trigger->pattern);
    }
}
