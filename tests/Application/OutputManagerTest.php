<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Application;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Application\OutputManager;
use Xocdr\Tui\Contracts\OutputManagerInterface;
use Xocdr\Tui\Rendering\Lifecycle\ApplicationLifecycle;

class OutputManagerTest extends TestCase
{
    private ApplicationLifecycle $lifecycle;

    private OutputManager $outputManager;

    protected function setUp(): void
    {
        $this->lifecycle = new ApplicationLifecycle();
        $this->outputManager = new OutputManager($this->lifecycle);
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(OutputManagerInterface::class, $this->outputManager);
    }

    public function testGetLastOutputReturnsEmptyStringInitially(): void
    {
        $this->assertEquals('', $this->outputManager->getLastOutput());
    }

    public function testSetLastOutput(): void
    {
        $this->outputManager->setLastOutput('Hello World');

        $this->assertEquals('Hello World', $this->outputManager->getLastOutput());
    }

    public function testClearResetsLastOutput(): void
    {
        $this->outputManager->setLastOutput('Some content');
        $this->outputManager->clear();

        $this->assertEquals('', $this->outputManager->getLastOutput());
    }

    public function testGetCapturedOutputReturnsNullWhenNotRunning(): void
    {
        $result = $this->outputManager->getCapturedOutput();

        $this->assertNull($result);
    }

    public function testMeasureElementReturnsNullWhenNotRunning(): void
    {
        $result = $this->outputManager->measureElement('test-id');

        $this->assertNull($result);
    }

    public function testClearDoesNotThrowWhenNotRunning(): void
    {
        // Should not throw when ext-tui instance is not available
        $this->outputManager->clear();

        $this->assertTrue(true);
    }
}
