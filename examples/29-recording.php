#!/usr/bin/env php
<?php

/**
 * Screen Recording Demo
 *
 * Demonstrates recording terminal sessions as asciicast v2 format.
 * The recording can be played back with asciinema or on asciinema.org.
 *
 * This example shows how to use the Recorder class to:
 * - Create a recording
 * - Capture frames
 * - Export/save as .cast file
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Support\Recording\Recorder;
use Xocdr\Tui\UI;

class RecordingDemo extends UI
{
    public function build(): Component
    {
        [$isRecording, $setIsRecording] = $this->state(false);
        [$frameCount, $setFrameCount] = $this->state(0);
        [$status, $setStatus] = $this->state('Ready to record');
        $recorderRef = $this->ref(null);

        $this->onKeyPress(function (string $input, $key) use (
            $isRecording,
            $setIsRecording,
            $setFrameCount,
            $setStatus,
            $recorderRef
        ) {
            switch ($input) {
                case 'r':
                    if (!$isRecording) {
                        // Start recording
                        $recorder = new Recorder(80, 24, 'TUI Demo Recording');
                        $recorder->start();
                        $recorderRef->current = $recorder;
                        $setIsRecording(true);
                        $setFrameCount(0);
                        $setStatus('Recording...');
                    }
                    break;

                case 's':
                    if ($isRecording && $recorderRef->current !== null) {
                        // Stop and save
                        $recorder = $recorderRef->current;
                        $recorder->stop();

                        $filename = 'recording-' . date('Y-m-d-His') . '.cast';
                        if ($recorder->save($filename)) {
                            $setStatus("Saved to {$filename}");
                        } else {
                            $setStatus('Failed to save (ext-tui recording functions not available)');
                        }

                        $recorder->destroy();
                        $recorderRef->current = null;
                        $setIsRecording(false);
                    }
                    break;

                case 'c':
                    if ($isRecording && $recorderRef->current !== null) {
                        // Capture a frame
                        $recorder = $recorderRef->current;
                        $frame = 'Frame captured at ' . date('H:i:s') . "\n";
                        $recorder->capture($frame);
                        $setFrameCount(fn ($c) => $c + 1);
                    }
                    break;

                case 'p':
                    if ($isRecording && $recorderRef->current !== null) {
                        $recorder = $recorderRef->current;
                        if ($recorder->isPaused()) {
                            $recorder->resume();
                            $setStatus('Recording...');
                        } else {
                            $recorder->pause();
                            $setStatus('Paused');
                        }
                    }
                    break;

                case 'q':
                    // Clean up recorder if active
                    if ($recorderRef->current !== null) {
                        $recorderRef->current->destroy();
                    }
                    $this->exit();
                    break;
            }

            if ($key->escape) {
                if ($recorderRef->current !== null) {
                    $recorderRef->current->destroy();
                }
                $this->exit();
            }
        });

        $statusColor = $isRecording ? Color::Red : Color::Green;

        return Box::column([
            Text::create('Screen Recording Demo')->bold()->underline(),
            Newline::create(),

            Box::row([
                Text::create('Status: ')->bold(),
                Text::create($status)->color($statusColor),
                $isRecording ? Text::create(' [REC]')->color(Color::Red)->bold() : null,
            ]),

            Box::row([
                Text::create('Frames: ')->bold(),
                Text::create((string) $frameCount)->color(Color::Cyan),
            ]),

            Newline::create(),

            Text::create('Controls:')->bold(),
            Newline::create(),
            Text::create('  [r] Start recording')->dim(),
            Text::create('  [c] Capture frame')->dim(),
            Text::create('  [p] Pause/Resume')->dim(),
            Text::create('  [s] Stop and save')->dim(),

            Newline::create(),
            Text::create('Recordings are saved as asciicast v2 format (.cast)')->dim()->italic(),
            Text::create('Play with: asciinema play recording.cast')->dim()->italic(),
            Newline::create(),
            Text::create('Press q or ESC to exit')->dim(),
        ]);
    }
}

RecordingDemo::run();
