<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Testing;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\ExtTestRenderer;
use Xocdr\Tui\Support\Testing\TestKey;

class ExtTestRendererTest extends TestCase
{
    private ?ExtTestRenderer $renderer = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!function_exists('tui_test_create')) {
            $this->markTestSkipped('ext-tui testing functions not available');
        }

        $this->renderer = new ExtTestRenderer(80, 24);
    }

    protected function tearDown(): void
    {
        $this->renderer = null;
        parent::tearDown();
    }

    public function testExtensionIsAvailable(): void
    {
        $this->assertTrue($this->renderer->isExtensionAvailable());
    }

    public function testRenderReturnsself(): void
    {
        $result = $this->renderer->render(fn () => Text::create('Hello'));

        $this->assertSame($this->renderer, $result);
    }

    public function testGetOutputReturnsArray(): void
    {
        $this->renderer->render(fn () => Text::create('Test'));

        $output = $this->renderer->getOutput();

        $this->assertIsArray($output);
    }

    public function testToStringReturnsString(): void
    {
        $this->renderer->render(fn () => Text::create('Hello World'));

        $output = $this->renderer->toString();

        $this->assertIsString($output);
        $this->assertStringContainsString('Hello World', $output);
    }

    public function testContainsText(): void
    {
        $this->renderer->render(fn () => Text::create('Find me'));

        $this->assertTrue($this->renderer->containsText('Find me'));
        $this->assertFalse($this->renderer->containsText('Not here'));
    }

    public function testRenderBox(): void
    {
        $this->renderer->render(fn () => Box::column([
            Text::create('Line 1'),
            Text::create('Line 2'),
        ]));

        $output = $this->renderer->toString();

        $this->assertStringContainsString('Line 1', $output);
        $this->assertStringContainsString('Line 2', $output);
    }

    public function testDimensions(): void
    {
        $renderer = new ExtTestRenderer(120, 40);

        $this->assertEquals(120, $renderer->getWidth());
        $this->assertEquals(40, $renderer->getHeight());
    }

    public function testSendInputReturnsSelf(): void
    {
        $result = $this->renderer->sendInput('a');

        $this->assertSame($this->renderer, $result);
    }

    public function testSendKeyReturnsSelf(): void
    {
        $result = $this->renderer->sendKey(TestKey::ENTER);

        $this->assertSame($this->renderer, $result);
    }

    public function testTypeReturnsSelf(): void
    {
        $result = $this->renderer->type('hello');

        $this->assertSame($this->renderer, $result);
    }

    public function testAdvanceFrameReturnsSelf(): void
    {
        $this->renderer->render(fn () => Text::create('Test'));

        $result = $this->renderer->advanceFrame();

        $this->assertSame($this->renderer, $result);
    }

    public function testRunTimersReturnsSelf(): void
    {
        $this->renderer->render(fn () => Text::create('Test'));

        $result = $this->renderer->runTimers(100);

        $this->assertSame($this->renderer, $result);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $this->renderer->render(fn () => Text::create('No ID'));

        $result = $this->renderer->getById('nonexistent');

        $this->assertNull($result);
    }

    public function testGetByTextReturnsArray(): void
    {
        $this->renderer->render(fn () => Text::create('Searchable text'));

        $results = $this->renderer->getByText('Searchable');

        $this->assertIsArray($results);
    }

    public function testQueryByTextReturnsNullWhenNotFound(): void
    {
        $this->renderer->render(fn () => Text::create('Something'));

        $result = $this->renderer->queryByText('Not found');

        $this->assertNull($result);
    }

    public function testFluentApi(): void
    {
        $result = $this->renderer
            ->render(fn () => Text::create('Test'))
            ->sendInput('a')
            ->sendKey(TestKey::ENTER)
            ->advanceFrame()
            ->runTimers(100);

        $this->assertSame($this->renderer, $result);
    }
}
