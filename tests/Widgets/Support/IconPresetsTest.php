<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Support;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Widgets\Support\IconPresets;

class IconPresetsTest extends TestCase
{
    public function testGetSpinnerReturnsDotsFrames(): void
    {
        $frames = IconPresets::getSpinner('dots');

        $this->assertCount(10, $frames);
        $this->assertEquals('⠋', $frames[0]);
    }

    public function testGetSpinnerReturnsDefaultForUnknown(): void
    {
        $frames = IconPresets::getSpinner('nonexistent');

        $this->assertCount(10, $frames);
    }

    public function testGetStatusReturnsIcon(): void
    {
        $this->assertEquals('✓', IconPresets::getStatus('success'));
        $this->assertEquals('✗', IconPresets::getStatus('error'));
        $this->assertEquals('⚠', IconPresets::getStatus('warning'));
    }

    public function testGetStatusReturnsDefaultForUnknown(): void
    {
        $this->assertEquals('○', IconPresets::getStatus('nonexistent'));
    }

    public function testGetCommonReturnsIcon(): void
    {
        $this->assertEquals('📁', IconPresets::getCommon('folder'));
        $this->assertEquals('🚀', IconPresets::getCommon('rocket'));
    }

    public function testGetCommonReturnsQuestionForUnknown(): void
    {
        $this->assertEquals('?', IconPresets::getCommon('nonexistent'));
    }

    public function testHasSpinner(): void
    {
        $this->assertTrue(IconPresets::hasSpinner('dots'));
        $this->assertTrue(IconPresets::hasSpinner('line'));
        $this->assertFalse(IconPresets::hasSpinner('nonexistent'));
    }

    public function testSpinnerNames(): void
    {
        $names = IconPresets::spinnerNames();

        $this->assertContains('dots', $names);
        $this->assertContains('dots2', $names);
        $this->assertContains('line', $names);
        $this->assertContains('arc', $names);
    }

    public function testAllSpinnersHaveFrames(): void
    {
        foreach (IconPresets::spinnerNames() as $name) {
            $frames = IconPresets::getSpinner($name);
            $this->assertNotEmpty($frames, "Spinner '{$name}' should have frames");
        }
    }
}
