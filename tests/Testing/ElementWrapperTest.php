<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Testing;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Testing\ElementWrapper;

class ElementWrapperTest extends TestCase
{
    public function testGetText(): void
    {
        $wrapper = new ElementWrapper(['text' => 'Hello World']);

        $this->assertEquals('Hello World', $wrapper->getText());
    }

    public function testGetTextReturnsEmptyStringWhenMissing(): void
    {
        $wrapper = new ElementWrapper([]);

        $this->assertEquals('', $wrapper->getText());
    }

    public function testIsFocused(): void
    {
        $focused = new ElementWrapper(['focused' => true]);
        $notFocused = new ElementWrapper(['focused' => false]);
        $missing = new ElementWrapper([]);

        $this->assertTrue($focused->isFocused());
        $this->assertFalse($notFocused->isFocused());
        $this->assertFalse($missing->isFocused());
    }

    public function testIsFocusable(): void
    {
        $focusable = new ElementWrapper(['focusable' => true]);
        $notFocusable = new ElementWrapper(['focusable' => false]);

        $this->assertTrue($focusable->isFocusable());
        $this->assertFalse($notFocusable->isFocusable());
    }

    public function testIsVisible(): void
    {
        $visible = new ElementWrapper(['visible' => true]);
        $hidden = new ElementWrapper(['visible' => false]);
        $missing = new ElementWrapper([]);

        $this->assertTrue($visible->isVisible());
        $this->assertFalse($hidden->isVisible());
        $this->assertTrue($missing->isVisible()); // Default is true
    }

    public function testPosition(): void
    {
        $wrapper = new ElementWrapper(['x' => 10, 'y' => 20]);

        $this->assertEquals(10, $wrapper->getX());
        $this->assertEquals(20, $wrapper->getY());
    }

    public function testPositionDefaults(): void
    {
        $wrapper = new ElementWrapper([]);

        $this->assertEquals(0, $wrapper->getX());
        $this->assertEquals(0, $wrapper->getY());
    }

    public function testDimensions(): void
    {
        $wrapper = new ElementWrapper(['width' => 100, 'height' => 50]);

        $this->assertEquals(100, $wrapper->getWidth());
        $this->assertEquals(50, $wrapper->getHeight());
    }

    public function testGetBounds(): void
    {
        $wrapper = new ElementWrapper([
            'x' => 10,
            'y' => 20,
            'width' => 100,
            'height' => 50,
        ]);

        $bounds = $wrapper->getBounds();

        $this->assertEquals([
            'x' => 10,
            'y' => 20,
            'width' => 100,
            'height' => 50,
        ], $bounds);
    }

    public function testGetType(): void
    {
        $box = new ElementWrapper(['type' => 'Box']);
        $text = new ElementWrapper(['type' => 'Text']);
        $unknown = new ElementWrapper([]);

        $this->assertEquals('Box', $box->getType());
        $this->assertEquals('Text', $text->getType());
        $this->assertEquals('unknown', $unknown->getType());
    }

    public function testGetId(): void
    {
        $withId = new ElementWrapper(['id' => 'my-element']);
        $withoutId = new ElementWrapper([]);

        $this->assertEquals('my-element', $withId->getId());
        $this->assertNull($withoutId->getId());
    }

    public function testGetStyles(): void
    {
        $wrapper = new ElementWrapper([
            'styles' => [
                'color' => '#ff0000',
                'bold' => true,
            ],
        ]);

        $styles = $wrapper->getStyles();

        $this->assertEquals('#ff0000', $styles['color']);
        $this->assertTrue($styles['bold']);
    }

    public function testGetStyle(): void
    {
        $wrapper = new ElementWrapper([
            'styles' => [
                'color' => '#ff0000',
            ],
        ]);

        $this->assertEquals('#ff0000', $wrapper->getStyle('color'));
        $this->assertNull($wrapper->getStyle('nonexistent'));
    }

    public function testGetChildCount(): void
    {
        $withChildren = new ElementWrapper(['childCount' => 3]);
        $noChildren = new ElementWrapper(['childCount' => 0]);
        $missing = new ElementWrapper([]);

        $this->assertEquals(3, $withChildren->getChildCount());
        $this->assertEquals(0, $noChildren->getChildCount());
        $this->assertEquals(0, $missing->getChildCount());
    }

    public function testHasChildren(): void
    {
        $withChildren = new ElementWrapper(['childCount' => 3]);
        $noChildren = new ElementWrapper(['childCount' => 0]);

        $this->assertTrue($withChildren->hasChildren());
        $this->assertFalse($noChildren->hasChildren());
    }

    public function testToArray(): void
    {
        $data = [
            'id' => 'test',
            'type' => 'Box',
            'x' => 10,
            'y' => 20,
        ];

        $wrapper = new ElementWrapper($data);

        $this->assertEquals($data, $wrapper->toArray());
    }

    public function testHas(): void
    {
        $wrapper = new ElementWrapper(['id' => 'test']);

        $this->assertTrue($wrapper->has('id'));
        $this->assertFalse($wrapper->has('nonexistent'));
    }

    public function testGet(): void
    {
        $wrapper = new ElementWrapper(['custom' => 'value']);

        $this->assertEquals('value', $wrapper->get('custom'));
        $this->assertNull($wrapper->get('nonexistent'));
        $this->assertEquals('default', $wrapper->get('nonexistent', 'default'));
    }
}
