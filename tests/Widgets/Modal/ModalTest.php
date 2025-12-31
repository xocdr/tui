<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Modal;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Modal\Modal;
use Xocdr\Tui\Widgets\Modal\PermissionDialog;

/**
 * Concrete implementation of Modal for testing.
 */
class TestModal extends Modal
{
    private string $content = 'Test content';

    public static function create(): self
    {
        return new self();
    }

    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    protected function buildContent(): Component
    {
        return new Text($this->content);
    }
}

class ModalTest extends TuiTestCase
{
    public function testModalCanBeCreated(): void
    {
        $modal = TestModal::create();

        $this->assertInstanceOf(Modal::class, $modal);
    }

    public function testModalFluentChaining(): void
    {
        $modal = TestModal::create()
            ->title('Test Title')
            ->border('double')
            ->width(60)
            ->padding(2)
            ->borderColor('cyan')
            ->titleColor('yellow')
            ->closable(true)
            ->onClose(fn () => null);

        $this->assertInstanceOf(Modal::class, $modal);
    }

    public function testModalRendersWithBorder(): void
    {
        $widget = $this->createWidget(
            TestModal::create()
                ->title('Test')
                ->content('Hello World')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testModalRendersWithoutBorder(): void
    {
        $widget = $this->createWidget(
            TestModal::create()
                ->border(false)
                ->content('No border')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testPermissionDialogIsWidget(): void
    {
        $dialog = PermissionDialog::create();

        // PermissionDialog is a standalone Widget (not extending Modal)
        // to work around issues with state management in abstract base classes
        $this->assertInstanceOf(\Xocdr\Tui\Widgets\Widget::class, $dialog);
    }

    public function testPermissionDialogRendersButtons(): void
    {
        $widget = $this->createWidget(
            PermissionDialog::create()
                ->title('Permission Required')
                ->message('Allow this action?')
                ->allowLabel('Yes')
                ->denyLabel('No')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testPermissionDialogFluentChaining(): void
    {
        $dialog = PermissionDialog::create()
            ->title('Confirm')
            ->message('Are you sure?')
            ->allowLabel('Confirm')
            ->denyLabel('Cancel')
            ->width(40)
            ->border('single')
            ->onAllow(fn () => null)
            ->onDeny(fn () => null);

        $this->assertInstanceOf(PermissionDialog::class, $dialog);
    }
}
