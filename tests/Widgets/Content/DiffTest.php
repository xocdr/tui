<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Content;

use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Content\Diff;

class DiffTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $diff = Diff::create();

        $this->assertInstanceOf(Diff::class, $diff);
    }

    public function testRendersAddedLines(): void
    {
        $widget = $this->createWidget(
            Diff::create()
                ->old("line1\nline2")
                ->new("line1\nline2\nline3")
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testFluentChaining(): void
    {
        $diff = Diff::create()
            ->old('original')
            ->new('modified')
            ->lineNumbers(true)
            ->addedColor('green')
            ->removedColor('red')
            ->contextLines(3);

        $this->assertInstanceOf(Diff::class, $diff);
    }

    public function testWordDiffChaining(): void
    {
        $diff = Diff::create()
            ->old('The quick brown fox')
            ->new('The slow brown dog')
            ->wordDiff(true);

        $this->assertInstanceOf(Diff::class, $diff);
    }

    public function testWordDiffRendersOutput(): void
    {
        $widget = $this->createWidget(
            Diff::create()
                ->old('Hello world')
                ->new('Hello universe')
                ->wordDiff(true)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testWordDiffWithLineNumbers(): void
    {
        $widget = $this->createWidget(
            Diff::create()
                ->old("line1\nline2")
                ->new("line1\nmodified")
                ->wordDiff(true)
                ->lineNumbers(true)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testFilenameHeader(): void
    {
        $widget = $this->createWidget(
            Diff::create()
                ->old('old content')
                ->new('new content')
                ->filename('src/example.php')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }
}
