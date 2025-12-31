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
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
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

        return new Box([
            new BoxColumn([
                (new Text('Screen Recording Demo'))->bold()->underline(),
                new Newline(),

                new BoxRow(array_filter([
                    (new Text('Status: '))->bold(),
                    (new Text($status))->color($statusColor),
                    $isRecording ? (new Text(' [REC]'))->color(Color::Red)->bold() : null,
                ])),

                new BoxRow([
                    (new Text('Frames: '))->bold(),
                    (new Text((string) $frameCount))->color(Color::Cyan),
                ]),

                new Newline(),

                (new Text('Controls:'))->bold(),
                new Newline(),
                (new Text('  [r] Start recording'))->dim(),
                (new Text('  [c] Capture frame'))->dim(),
                (new Text('  [p] Pause/Resume'))->dim(),
                (new Text('  [s] Stop and save'))->dim(),

                new Newline(),
                (new Text('Recordings are saved as asciicast v2 format (.cast)'))->dim()->italic(),
                (new Text('Play with: asciinema play recording.cast'))->dim()->italic(),
                new Newline(),
                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new RecordingDemo())->run();
