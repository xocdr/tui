<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Input;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Widgets\Input\SelectOption;

class SelectOptionTest extends TestCase
{
    public function testConstructWithRequiredParams(): void
    {
        $option = new SelectOption('value', 'Label');

        $this->assertEquals('value', $option->value);
        $this->assertEquals('Label', $option->label);
        $this->assertNull($option->description);
        $this->assertNull($option->icon);
        $this->assertFalse($option->disabled);
    }

    public function testConstructWithAllParams(): void
    {
        $option = new SelectOption(
            value: 'value',
            label: 'Label',
            description: 'A description',
            icon: '★',
            disabled: true
        );

        $this->assertEquals('value', $option->value);
        $this->assertEquals('Label', $option->label);
        $this->assertEquals('A description', $option->description);
        $this->assertEquals('★', $option->icon);
        $this->assertTrue($option->disabled);
    }

    public function testFromWithStringData(): void
    {
        $option = SelectOption::from('key', 'Label Text');

        $this->assertEquals('key', $option->value);
        $this->assertEquals('Label Text', $option->label);
    }

    public function testFromWithArrayData(): void
    {
        $option = SelectOption::from('key', [
            'label' => 'Option Label',
            'description' => 'Option description',
            'icon' => '🔥',
            'disabled' => true,
        ]);

        $this->assertEquals('key', $option->value);
        $this->assertEquals('Option Label', $option->label);
        $this->assertEquals('Option description', $option->description);
        $this->assertEquals('🔥', $option->icon);
        $this->assertTrue($option->disabled);
    }

    public function testFromWithPartialArrayData(): void
    {
        $option = SelectOption::from('key', [
            'label' => 'Only Label',
        ]);

        $this->assertEquals('key', $option->value);
        $this->assertEquals('Only Label', $option->label);
        $this->assertNull($option->description);
        $this->assertNull($option->icon);
        $this->assertFalse($option->disabled);
    }

    public function testFromWithMissingLabelUsesValue(): void
    {
        $option = SelectOption::from('myvalue', []);

        $this->assertEquals('myvalue', $option->value);
        $this->assertEquals('myvalue', $option->label);
    }

    public function testValueCanBeInteger(): void
    {
        $option = new SelectOption(42, 'Forty Two');

        $this->assertEquals(42, $option->value);
        $this->assertEquals('Forty Two', $option->label);
    }
}
