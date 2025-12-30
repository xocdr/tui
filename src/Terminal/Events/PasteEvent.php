<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal\Events;

/**
 * Event dispatched when text is pasted via bracketed paste mode.
 *
 * Bracketed paste mode allows the terminal to distinguish between
 * typed input and pasted content, enabling proper handling of
 * multi-line pastes and special characters.
 *
 * @example
 * $hooks->onPaste(function(PasteEvent $event) {
 *     $pastedText = $event->getText();
 *     // Handle pasted content
 * });
 */
class PasteEvent extends Event
{
    public function __construct(
        public readonly string $text
    ) {
    }

    /**
     * Get the pasted text content.
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Get the number of lines in the pasted text.
     */
    public function getLineCount(): int
    {
        return substr_count($this->text, "\n") + 1;
    }

    /**
     * Check if the paste contains multiple lines.
     */
    public function isMultiLine(): bool
    {
        return str_contains($this->text, "\n");
    }

    /**
     * Get the pasted text split into lines.
     *
     * @return array<string>
     */
    public function getLines(): array
    {
        return explode("\n", $this->text);
    }

    /**
     * Get the length of the pasted text.
     */
    public function getLength(): int
    {
        return strlen($this->text);
    }
}
