<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Support\Recording;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Support\Recording\Recorder;

class RecorderTest extends TestCase
{
    public function testConstructorWithDefaults(): void
    {
        $recorder = new Recorder();
        $dimensions = $recorder->getDimensions();

        $this->assertSame(80, $dimensions['width']);
        $this->assertSame(24, $dimensions['height']);
        $this->assertNull($recorder->getTitle());
    }

    public function testConstructorWithCustomValues(): void
    {
        $recorder = new Recorder(120, 40, 'Test Recording');
        $dimensions = $recorder->getDimensions();

        $this->assertSame(120, $dimensions['width']);
        $this->assertSame(40, $dimensions['height']);
        $this->assertSame('Test Recording', $recorder->getTitle());
    }

    public function testStartRecording(): void
    {
        $recorder = new Recorder();

        $this->assertFalse($recorder->isRecording());
        $result = $recorder->start();
        $this->assertTrue($result);
        $this->assertTrue($recorder->isRecording());
    }

    public function testCannotStartTwice(): void
    {
        $recorder = new Recorder();
        $recorder->start();

        $result = $recorder->start();
        $this->assertFalse($result);
    }

    public function testPauseRecording(): void
    {
        $recorder = new Recorder();
        $recorder->start();

        $this->assertFalse($recorder->isPaused());
        $result = $recorder->pause();
        $this->assertTrue($result);
        $this->assertTrue($recorder->isPaused());
        $this->assertFalse($recorder->isRecording());
    }

    public function testCannotPauseWithoutStarting(): void
    {
        $recorder = new Recorder();

        $result = $recorder->pause();
        $this->assertFalse($result);
    }

    public function testResumeRecording(): void
    {
        $recorder = new Recorder();
        $recorder->start();
        $recorder->pause();

        $this->assertTrue($recorder->isPaused());
        $result = $recorder->resume();
        $this->assertTrue($result);
        $this->assertTrue($recorder->isRecording());
        $this->assertFalse($recorder->isPaused());
    }

    public function testCannotResumeWithoutPausing(): void
    {
        $recorder = new Recorder();
        $recorder->start();

        $result = $recorder->resume();
        $this->assertFalse($result);
    }

    public function testStopRecording(): void
    {
        $recorder = new Recorder();
        $recorder->start();

        $this->assertFalse($recorder->isStopped());
        $result = $recorder->stop();
        $this->assertTrue($result);
        $this->assertTrue($recorder->isStopped());
        $this->assertFalse($recorder->isRecording());
    }

    public function testStopFromPaused(): void
    {
        $recorder = new Recorder();
        $recorder->start();
        $recorder->pause();

        $result = $recorder->stop();
        $this->assertTrue($result);
        $this->assertTrue($recorder->isStopped());
    }

    public function testCannotStopWithoutStarting(): void
    {
        $recorder = new Recorder();

        $result = $recorder->stop();
        $this->assertFalse($result);
    }

    public function testCaptureReturnsFalseWithoutExtension(): void
    {
        if (function_exists('tui_record_capture')) {
            $this->markTestSkipped('ext-tui is loaded');
        }

        $recorder = new Recorder();
        $recorder->start();

        $result = $recorder->capture('test frame data');
        $this->assertFalse($result);
    }

    public function testCaptureReturnsFalseWhenNotRecording(): void
    {
        $recorder = new Recorder();

        $result = $recorder->capture('test data');
        $this->assertFalse($result);
    }

    public function testCaptureReturnsFalseWhenPaused(): void
    {
        $recorder = new Recorder();
        $recorder->start();
        $recorder->pause();

        $result = $recorder->capture('test data');
        $this->assertFalse($result);
    }

    public function testGetDurationWithoutExtension(): void
    {
        if (function_exists('tui_record_duration')) {
            $this->markTestSkipped('ext-tui is loaded');
        }

        $recorder = new Recorder();
        $this->assertSame(0.0, $recorder->getDuration());
    }

    public function testGetFrameCountWithoutExtension(): void
    {
        if (function_exists('tui_record_frame_count')) {
            $this->markTestSkipped('ext-tui is loaded');
        }

        $recorder = new Recorder();
        $this->assertSame(0, $recorder->getFrameCount());
    }

    public function testExportWithoutExtension(): void
    {
        if (function_exists('tui_record_export')) {
            $this->markTestSkipped('ext-tui is loaded');
        }

        $recorder = new Recorder();
        $this->assertNull($recorder->export());
    }

    public function testSaveWithoutExtension(): void
    {
        if (function_exists('tui_record_save')) {
            $this->markTestSkipped('ext-tui is loaded');
        }

        $recorder = new Recorder();
        $result = $recorder->save('/tmp/test.cast');
        $this->assertFalse($result);
    }

    public function testStateTransitions(): void
    {
        $recorder = new Recorder();

        // Idle state
        $this->assertFalse($recorder->isRecording());
        $this->assertFalse($recorder->isPaused());
        $this->assertFalse($recorder->isStopped());

        // Start -> Recording
        $recorder->start();
        $this->assertTrue($recorder->isRecording());
        $this->assertFalse($recorder->isPaused());
        $this->assertFalse($recorder->isStopped());

        // Pause -> Paused
        $recorder->pause();
        $this->assertFalse($recorder->isRecording());
        $this->assertTrue($recorder->isPaused());
        $this->assertFalse($recorder->isStopped());

        // Resume -> Recording
        $recorder->resume();
        $this->assertTrue($recorder->isRecording());
        $this->assertFalse($recorder->isPaused());
        $this->assertFalse($recorder->isStopped());

        // Stop -> Stopped
        $recorder->stop();
        $this->assertFalse($recorder->isRecording());
        $this->assertFalse($recorder->isPaused());
        $this->assertTrue($recorder->isStopped());
    }

    public function testDestroyCanBeCalledMultipleTimes(): void
    {
        $recorder = new Recorder();
        $recorder->start();

        // Should not throw
        $recorder->destroy();
        $recorder->destroy();

        $this->assertTrue(true);
    }

    public function testGetDimensions(): void
    {
        $recorder = new Recorder(100, 30);
        $dimensions = $recorder->getDimensions();

        $this->assertIsArray($dimensions);
        $this->assertArrayHasKey('width', $dimensions);
        $this->assertArrayHasKey('height', $dimensions);
        $this->assertSame(100, $dimensions['width']);
        $this->assertSame(30, $dimensions['height']);
    }
}
