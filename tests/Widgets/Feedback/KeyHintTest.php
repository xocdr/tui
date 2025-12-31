<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Feedback;

use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Feedback\KeyHint;

class KeyHintTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $hint = KeyHint::create();

        $this->assertInstanceOf(KeyHint::class, $hint);
    }

    public function testRendersKeyAndAction(): void
    {
        $widget = $this->createWidget(
            KeyHint::create()
                ->key('Enter')
                ->action('Submit')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testFluentChaining(): void
    {
        $hint = KeyHint::create()
            ->key('Ctrl+S')
            ->action('Save')
            ->keyColor('cyan')
            ->actionColor('gray')
            ->separator(' - ');

        $this->assertInstanceOf(KeyHint::class, $hint);
    }

    public function testGridDisplayMode(): void
    {
        $hint = KeyHint::create()
            ->hints([
                ['key' => 'Enter', 'action' => 'Submit'],
                ['key' => 'Esc', 'action' => 'Cancel'],
                ['key' => 'Tab', 'action' => 'Next'],
                ['key' => 'Shift+Tab', 'action' => 'Previous'],
            ])
            ->grid(2)
            ->columnWidth(25);

        $this->assertInstanceOf(KeyHint::class, $hint);
    }

    public function testGroupedDisplayMode(): void
    {
        $hint = KeyHint::create()
            ->hints([
                ['key' => 'j', 'action' => 'Down', 'group' => 'Navigation'],
                ['key' => 'k', 'action' => 'Up', 'group' => 'Navigation'],
                ['key' => 'e', 'action' => 'Edit', 'group' => 'Actions'],
                ['key' => 'd', 'action' => 'Delete', 'group' => 'Actions'],
            ])
            ->grouped()
            ->showGroupHeaders(true)
            ->groupHeaderColor('yellow');

        $this->assertInstanceOf(KeyHint::class, $hint);
    }

    public function testAddWithGroup(): void
    {
        $hint = KeyHint::create()
            ->add('q', 'Quit', 'General')
            ->add('?', 'Help', 'General')
            ->grouped();

        $this->assertInstanceOf(KeyHint::class, $hint);
    }

    public function testInlineDisplayMode(): void
    {
        $hint = KeyHint::create()
            ->add('Enter', 'Submit')
            ->add('Esc', 'Cancel')
            ->inline();

        $this->assertInstanceOf(KeyHint::class, $hint);
    }

    public function testRendersGridLayout(): void
    {
        $widget = $this->createWidget(
            KeyHint::create()
                ->hints([
                    ['key' => 'a', 'action' => 'Action A'],
                    ['key' => 'b', 'action' => 'Action B'],
                ])
                ->grid(2)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testRendersGroupedLayout(): void
    {
        $widget = $this->createWidget(
            KeyHint::create()
                ->hints([
                    ['key' => 'j', 'action' => 'Down', 'group' => 'Navigation'],
                    ['key' => 'k', 'action' => 'Up', 'group' => 'Navigation'],
                ])
                ->grouped()
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }
}
