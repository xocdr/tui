<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback;

use Xocdr\Tui\Widgets\Support\Constants;

class StatusBarContext
{
    /** @var array<string, mixed> */
    public array $data;

    public int $terminalWidth;

    public float $timestamp;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        array $data = [],
        int $terminalWidth = Constants::DEFAULT_TERMINAL_WIDTH,
        ?float $timestamp = null,
    ) {
        $this->data = $data;
        $this->terminalWidth = $terminalWidth;
        $this->timestamp = $timestamp ?? microtime(true);
    }
}
