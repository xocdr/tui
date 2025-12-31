<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Content;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Widgets\Content\OutputBlock;

class OutputBlockTest extends TestCase
{
    public function testCreateReturnsInstance(): void
    {
        $block = OutputBlock::create();

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testCreateWithContent(): void
    {
        $block = OutputBlock::create('Command output here');

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testStdoutFactoryMethod(): void
    {
        $block = OutputBlock::stdout('Standard output');

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testStderrFactoryMethod(): void
    {
        $block = OutputBlock::stderr('Error output');

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testContentCanBeSet(): void
    {
        $block = OutputBlock::create()
            ->content('Output text');

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testCommandCanBeSet(): void
    {
        $block = OutputBlock::create()
            ->command('npm install')
            ->content('Installing packages...');

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testExitCodeCanBeSet(): void
    {
        $block = OutputBlock::create()
            ->content('Done')
            ->exitCode(0);

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testContentCanBeAppended(): void
    {
        $block = OutputBlock::create('Line 1')
            ->append("\nLine 2");

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testTypeCanBeStdout(): void
    {
        $block = OutputBlock::create()->type('stdout');

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testTypeCanBeStderr(): void
    {
        $block = OutputBlock::create()->type('stderr');

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testStreamingCanBeEnabled(): void
    {
        $block = OutputBlock::create()->streaming(true);

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testShowHeaderCanBeDisabled(): void
    {
        $block = OutputBlock::create()->showHeader(false);

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testShowExitCodeCanBeDisabled(): void
    {
        $block = OutputBlock::create()->showExitCode(false);

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testShowTimestampCanBeEnabled(): void
    {
        $block = OutputBlock::create()
            ->showTimestamp()
            ->timestamp('12:34:56');

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testMaxLinesCanBeSet(): void
    {
        $block = OutputBlock::create()->maxLines(50);

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testScrollableCanBeEnabled(): void
    {
        $block = OutputBlock::create()->scrollable(true);

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testWrapCanBeDisabled(): void
    {
        $block = OutputBlock::create()->wrap(false);

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testBorderCanBeEnabled(): void
    {
        $block = OutputBlock::create()->border(true);

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testColorsCanBeCustomized(): void
    {
        $block = OutputBlock::create()
            ->stdoutColor('white')
            ->stderrColor('red')
            ->commandColor('cyan')
            ->successColor('green')
            ->errorColor('red');

        $this->assertInstanceOf(OutputBlock::class, $block);
    }

    public function testFluentChaining(): void
    {
        $block = OutputBlock::create()
            ->command('git status')
            ->content('On branch main')
            ->type('stdout')
            ->exitCode(0)
            ->streaming(false)
            ->showHeader(true)
            ->showExitCode(true)
            ->showTimestamp(true)
            ->timestamp('2024-01-01 12:00:00')
            ->maxLines(100)
            ->scrollable(true)
            ->border('single');

        $this->assertInstanceOf(OutputBlock::class, $block);
    }
}
