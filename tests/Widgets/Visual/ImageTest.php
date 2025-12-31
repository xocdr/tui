<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Visual;

use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Visual\Image;

class ImageTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $image = Image::create();

        $this->assertInstanceOf(Image::class, $image);
    }

    public function testFluentChaining(): void
    {
        $image = Image::create()
            ->path('/path/to/image.png')
            ->width(40)
            ->height(20)
            ->preserveAspectRatio(true);

        $this->assertInstanceOf(Image::class, $image);
    }
}
