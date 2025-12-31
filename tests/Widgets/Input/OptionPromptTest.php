<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Input;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Widgets\Input\OptionPrompt;
use Xocdr\Tui\Widgets\Input\OptionPromptOption;

class OptionPromptTest extends TestCase
{
    public function testCreateReturnsInstance(): void
    {
        $prompt = OptionPrompt::create();

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testQuestionCanBeSet(): void
    {
        $prompt = OptionPrompt::create()
            ->question('Are you sure?');

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testDescriptionCanBeSet(): void
    {
        $prompt = OptionPrompt::create()
            ->description('This action cannot be undone');

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testVariantCanBeInline(): void
    {
        $prompt = OptionPrompt::create()
            ->variant('inline');

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testVariantCanBeModal(): void
    {
        $prompt = OptionPrompt::create()
            ->variant('modal');

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testBorderCanBeEnabled(): void
    {
        $prompt = OptionPrompt::create()
            ->border(true);

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testBorderCanBeStyled(): void
    {
        $prompt = OptionPrompt::create()
            ->border('double');

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testTitleCanBeSet(): void
    {
        $prompt = OptionPrompt::create()
            ->title('Confirmation');

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testOptionsCanBeSetAsArray(): void
    {
        $prompt = OptionPrompt::create()
            ->options([
                ['key' => 'y', 'label' => 'Yes'],
                ['key' => 'n', 'label' => 'No'],
            ]);

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testOptionsAcceptsOptionObjects(): void
    {
        $prompt = OptionPrompt::create()
            ->options([
                new OptionPromptOption('y', 'Yes'),
                new OptionPromptOption('n', 'No'),
            ]);

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testAddOptionAddsOption(): void
    {
        $prompt = OptionPrompt::create()
            ->addOption('y', 'Yes', 'Confirm the action')
            ->addOption('n', 'No', 'Cancel the action');

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testContentCanBeSet(): void
    {
        $prompt = OptionPrompt::create()
            ->content('Additional content here');

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testWithInputEnablesInputForOption(): void
    {
        $prompt = OptionPrompt::create()
            ->addOption('r', 'Reject')
            ->withInput('r');

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testInputPlaceholderCanBeSet(): void
    {
        $prompt = OptionPrompt::create()
            ->inputPlaceholder('Enter reason...');

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testInputLabelCanBeSet(): void
    {
        $prompt = OptionPrompt::create()
            ->inputLabel('Reason: ');

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testOnSelectCallbackCanBeSet(): void
    {
        $prompt = OptionPrompt::create()
            ->onSelect(fn ($option, $input) => null);

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testWidthCanBeSet(): void
    {
        $prompt = OptionPrompt::create()
            ->width(60);

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testCenterCanBeDisabled(): void
    {
        $prompt = OptionPrompt::create()
            ->center(false);

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testSelectedColorCanBeSet(): void
    {
        $prompt = OptionPrompt::create()
            ->selectedColor('green');

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }

    public function testFluentChaining(): void
    {
        $prompt = OptionPrompt::create()
            ->question('Proceed with action?')
            ->description('This will modify files')
            ->variant('modal')
            ->border('double')
            ->title('Confirmation')
            ->addOption('y', 'Yes')
            ->addOption('n', 'No')
            ->addOption('r', 'Reject', 'Reject with reason')
            ->withInput('r')
            ->inputLabel('Reason: ')
            ->selectedColor('cyan')
            ->onSelect(fn ($opt, $input) => null);

        $this->assertInstanceOf(OptionPrompt::class, $prompt);
    }
}

class OptionPromptOptionTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $option = new OptionPromptOption(
            key: 'y',
            label: 'Yes',
            description: 'Confirm',
            value: 'confirmed',
            requiresInput: false,
        );

        $this->assertEquals('y', $option->key);
        $this->assertEquals('Yes', $option->label);
        $this->assertEquals('Confirm', $option->description);
        $this->assertEquals('confirmed', $option->value);
        $this->assertFalse($option->requiresInput);
    }

    public function testValueDefaultsToKey(): void
    {
        $option = new OptionPromptOption('y', 'Yes');

        $this->assertEquals('y', $option->value);
    }

    public function testFromCreatesFromArray(): void
    {
        $option = OptionPromptOption::from([
            'key' => 'n',
            'label' => 'No',
            'description' => 'Cancel action',
            'requiresInput' => true,
        ]);

        $this->assertEquals('n', $option->key);
        $this->assertEquals('No', $option->label);
        $this->assertEquals('Cancel action', $option->description);
        $this->assertTrue($option->requiresInput);
    }
}
