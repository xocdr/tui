<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Hooks;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Contracts\HooksInterface;
use Xocdr\Tui\Hooks\Hooks;
use Xocdr\Tui\Hooks\HooksAwareTrait;

class HooksAwareTraitTest extends TestCase
{
    public function testSetAndGetHooks(): void
    {
        $component = new class () implements HooksAwareInterface {
            use HooksAwareTrait;
        };

        $hooks = new Hooks();
        $component->setHooks($hooks);

        $this->assertSame($hooks, $component->getHooks());
    }

    public function testGetHooksCreatesDefaultInstance(): void
    {
        $component = new class () implements HooksAwareInterface {
            use HooksAwareTrait;
        };

        $hooks = $component->getHooks();

        $this->assertInstanceOf(HooksInterface::class, $hooks);
    }

    public function testHooksMethodReturnsSameAsGetHooks(): void
    {
        $component = new class () implements HooksAwareInterface {
            use HooksAwareTrait;

            public function callHooks(): HooksInterface
            {
                return $this->hooks();
            }
        };

        $this->assertSame($component->getHooks(), $component->callHooks());
    }
}
