<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Modal;

use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Modal\PermissionDialog;

class PermissionDialogTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $dialog = PermissionDialog::create();

        $this->assertInstanceOf(PermissionDialog::class, $dialog);
    }

    public function testRendersTitle(): void
    {
        $widget = $this->createWidget(
            PermissionDialog::create()
                ->title('Permission Required')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testFluentChaining(): void
    {
        $dialog = PermissionDialog::create()
            ->title('Confirm')
            ->message('Allow this action?')
            ->allowLabel('Allow')
            ->denyLabel('Deny')
            ->onAllow(fn () => null)
            ->onDeny(fn () => null);

        $this->assertInstanceOf(PermissionDialog::class, $dialog);
    }
}
