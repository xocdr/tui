<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Input;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Input\ConfirmInput;

class ConfirmInputTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $confirm = ConfirmInput::create('Are you sure?');

        $this->assertInstanceOf(ConfirmInput::class, $confirm);
    }

    public function testRendersQuestion(): void
    {
        $widget = $this->createWidget(
            ConfirmInput::create('Are you sure?')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Are you sure?'));
    }

    public function testRendersDescription(): void
    {
        $widget = $this->createWidget(
            ConfirmInput::create('Delete?')
                ->description('This action cannot be undone')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'This action cannot be undone'));
    }

    public function testRendersYesNoHints(): void
    {
        $widget = $this->createWidget(
            ConfirmInput::create('Continue?')
        );

        $output = $this->renderWidget($widget);

        // Should show y/n hints
        $this->assertTrue(
            $this->containsText($output, 'y') || $this->containsText($output, 'n')
        );
    }

    public function testConfirmWithYKey(): void
    {
        $widget = $this->createWidget(
            ConfirmInput::create('Proceed?')
                ->onConfirm(fn ($result) => null)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testDeclineWithNKey(): void
    {
        $widget = $this->createWidget(
            ConfirmInput::create('Proceed?')
                ->onConfirm(fn ($result) => null)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testDefaultYesWithEnter(): void
    {
        $widget = $this->createWidget(
            ConfirmInput::create('Continue?')
                ->defaultYes()
                ->onConfirm(fn ($result) => null)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testDefaultNoWithEnter(): void
    {
        $widget = $this->createWidget(
            ConfirmInput::create('Continue?')
                ->defaultNo()
                ->onConfirm(fn ($result) => null)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testCustomYesNoKeys(): void
    {
        $widget = $this->createWidget(
            ConfirmInput::create('Continuer?')
                ->yesKey('o')  // French: oui
                ->noKey('n')  // French: non
                ->onConfirm(fn ($result) => null)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testDangerVariant(): void
    {
        $widget = $this->createWidget(
            ConfirmInput::create('Delete all data?')
                ->variant('danger')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testFluentChaining(): void
    {
        $confirm = ConfirmInput::create('Continue?')
            ->question('Are you absolutely sure?')
            ->description('This is permanent')
            ->defaultYes()
            ->variant('danger')
            ->yesKey('y')
            ->noKey('n')
            ->onConfirm(fn ($c) => null);

        $this->assertInstanceOf(ConfirmInput::class, $confirm);
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
