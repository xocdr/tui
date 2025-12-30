<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Recording;

/**
 * Screen recorder for terminal sessions.
 *
 * Records terminal output as asciicast v2 format, compatible with
 * asciinema player and asciinema.org.
 *
 * @example
 * // Create and start recording
 * $recorder = new Recorder(80, 24, 'My Demo');
 * $recorder->start();
 *
 * // Capture frames during your application
 * $recorder->capture($terminalOutput);
 *
 * // Stop and save
 * $recorder->stop();
 * $recorder->save('demo.cast');
 *
 * // Or export as JSON string
 * $json = $recorder->export();
 */
final class Recorder
{
    /**
     * Recording states.
     */
    private const STATE_IDLE = 0;
    private const STATE_RECORDING = 1;
    private const STATE_PAUSED = 2;
    private const STATE_STOPPED = 3;

    private int $width;

    private int $height;

    private ?string $title;

    private int $state = self::STATE_IDLE;

    /** @var resource|null */
    private mixed $resource = null;

    /**
     * Create a new recorder.
     *
     * @param int $width Terminal width in columns
     * @param int $height Terminal height in rows
     * @param string|null $title Optional recording title
     */
    public function __construct(int $width = 80, int $height = 24, ?string $title = null)
    {
        $this->width = $width;
        $this->height = $height;
        $this->title = $title;

        if (function_exists('tui_record_create')) {
            $this->resource = tui_record_create($width, $height, $title);
        }
    }

    /**
     * Start recording.
     *
     * @return bool True if recording started successfully
     */
    public function start(): bool
    {
        if ($this->state !== self::STATE_IDLE) {
            return false;
        }

        if ($this->resource !== null && function_exists('tui_record_start')) {
            $result = tui_record_start($this->resource);
            if ($result) {
                $this->state = self::STATE_RECORDING;
            }

            return $result;
        }

        $this->state = self::STATE_RECORDING;

        return true;
    }

    /**
     * Pause recording.
     *
     * @return bool True if recording was paused
     */
    public function pause(): bool
    {
        if ($this->state !== self::STATE_RECORDING) {
            return false;
        }

        if ($this->resource !== null && function_exists('tui_record_pause')) {
            $result = tui_record_pause($this->resource);
            if ($result) {
                $this->state = self::STATE_PAUSED;
            }

            return $result;
        }

        $this->state = self::STATE_PAUSED;

        return true;
    }

    /**
     * Resume recording.
     *
     * @return bool True if recording was resumed
     */
    public function resume(): bool
    {
        if ($this->state !== self::STATE_PAUSED) {
            return false;
        }

        if ($this->resource !== null && function_exists('tui_record_resume')) {
            $result = tui_record_resume($this->resource);
            if ($result) {
                $this->state = self::STATE_RECORDING;
            }

            return $result;
        }

        $this->state = self::STATE_RECORDING;

        return true;
    }

    /**
     * Stop recording.
     *
     * @return bool True if recording was stopped
     */
    public function stop(): bool
    {
        if ($this->state !== self::STATE_RECORDING && $this->state !== self::STATE_PAUSED) {
            return false;
        }

        if ($this->resource !== null && function_exists('tui_record_stop')) {
            $result = tui_record_stop($this->resource);
            if ($result) {
                $this->state = self::STATE_STOPPED;
            }

            return $result;
        }

        $this->state = self::STATE_STOPPED;

        return true;
    }

    /**
     * Capture a frame of terminal output.
     *
     * @param string $data The terminal output data for this frame
     * @return bool True if frame was captured
     */
    public function capture(string $data): bool
    {
        if ($this->state !== self::STATE_RECORDING) {
            return false;
        }

        if ($this->resource !== null && function_exists('tui_record_capture')) {
            return tui_record_capture($this->resource, $data);
        }

        return false;
    }

    /**
     * Get recording duration in seconds.
     *
     * @return float Duration in seconds
     */
    public function getDuration(): float
    {
        if ($this->resource !== null && function_exists('tui_record_duration')) {
            return tui_record_duration($this->resource);
        }

        return 0.0;
    }

    /**
     * Get the number of captured frames.
     *
     * @return int Frame count
     */
    public function getFrameCount(): int
    {
        if ($this->resource !== null && function_exists('tui_record_frame_count')) {
            return tui_record_frame_count($this->resource);
        }

        return 0;
    }

    /**
     * Export recording as asciicast v2 JSON.
     *
     * @return string|null JSON string or null if export failed
     */
    public function export(): ?string
    {
        if ($this->resource !== null && function_exists('tui_record_export')) {
            return tui_record_export($this->resource);
        }

        return null;
    }

    /**
     * Save recording to a file.
     *
     * @param string $path Path to save the .cast file
     * @return bool True if saved successfully
     */
    public function save(string $path): bool
    {
        if ($this->resource !== null && function_exists('tui_record_save')) {
            return tui_record_save($this->resource, $path);
        }

        // Fallback: export and write manually
        $json = $this->export();
        if ($json !== null) {
            $result = file_put_contents($path, $json);

            return $result !== false;
        }

        return false;
    }

    /**
     * Check if recording is currently in progress.
     *
     * @return bool True if recording
     */
    public function isRecording(): bool
    {
        return $this->state === self::STATE_RECORDING;
    }

    /**
     * Check if recording is paused.
     *
     * @return bool True if paused
     */
    public function isPaused(): bool
    {
        return $this->state === self::STATE_PAUSED;
    }

    /**
     * Check if recording has been stopped.
     *
     * @return bool True if stopped
     */
    public function isStopped(): bool
    {
        return $this->state === self::STATE_STOPPED;
    }

    /**
     * Get recording dimensions.
     *
     * @return array{width: int, height: int}
     */
    public function getDimensions(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
        ];
    }

    /**
     * Get recording title.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Clean up resources.
     */
    public function destroy(): void
    {
        if ($this->resource !== null && function_exists('tui_record_destroy')) {
            tui_record_destroy($this->resource);
            $this->resource = null;
        }
    }

    /**
     * Destructor - clean up resources.
     */
    public function __destruct()
    {
        $this->destroy();
    }
}
