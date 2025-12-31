<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Visual;

use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Visual\Shape;

class ShapeTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $shape = Shape::create();

        $this->assertInstanceOf(Shape::class, $shape);
    }

    public function testFluentChaining(): void
    {
        $shape = Shape::create()
            ->type('rectangle')
            ->width(10)
            ->height(5)
            ->color('cyan')
            ->filled(true);

        $this->assertInstanceOf(Shape::class, $shape);
    }
}
