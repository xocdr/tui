<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Input;

use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\TerminalManager;
use Xocdr\Tui\Widgets\Contracts\FocusableWidget;
use Xocdr\Tui\Widgets\Contracts\InteractiveWidget;
use Xocdr\Tui\Widgets\Support\Constants;
use Xocdr\Tui\Widgets\Support\Enums\CursorStyle;
use Xocdr\Tui\Widgets\Widget;

class Input extends Widget implements FocusableWidget, InteractiveWidget
{
    private string $value = '';

    private string $placeholder = '';

    private string $prompt = '> ';

    private string $hint = '';

    private string $hintStreaming = '';

    private bool $isFocused = false;

    private bool $autofocus = false;

    private int $tabIndex = 0;

    private CursorStyle $cursorStyle = CursorStyle::BLOCK;

    private ?string $cursorChar = null;

    private bool $cursorBlink = true;

    private int $blinkRate = Constants::DEFAULT_CURSOR_BLINK_RATE_MS;

    private bool $interactive = true;

    /** @var array<string> */
    private array $history = [];

    private bool $historyEnabled = true;

    private bool $masked = false;

    private string $maskChar = '"';

    /** @var callable|null */
    private $onSubmit = null;

    /** @var callable|null */
    private $onChange = null;

    /** @var callable|null */
    private $onCancel = null;

    /** @var callable|null */
    private $onFocus = null;

    /** @var callable|null */
    private $onBlur = null;

    /** @var callable|null */
    private $onKeyPress = null;

    private bool $useTerminalCursor = true;

    private ?TerminalManager $terminalManager = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function value(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function prompt(string $prompt): self
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function hint(string $hint): self
    {
        $this->hint = $hint;

        return $this;
    }

    public function hintStreaming(string $hint): self
    {
        $this->hintStreaming = $hint;

        return $this;
    }

    public function isFocused(bool $focused): self
    {
        $this->isFocused = $focused;

        return $this;
    }

    public function autofocus(bool $autofocus = true): self
    {
        $this->autofocus = $autofocus;

        return $this;
    }

    public function tabIndex(int $index): self
    {
        $this->tabIndex = $index;

        return $this;
    }

    /**
     * @throws \ValueError If the cursor style string is invalid
     */
    public function cursorStyle(CursorStyle|string $style): self
    {
        if (is_string($style)) {
            $style = CursorStyle::from($style);
        }
        $this->cursorStyle = $style;

        return $this;
    }

    public function interactive(bool $interactive = true): self
    {
        $this->interactive = $interactive;

        return $this;
    }

    public function cursorChar(string $char): self
    {
        $this->cursorChar = $char;

        return $this;
    }

    public function cursorBlink(bool $blink = true): self
    {
        $this->cursorBlink = $blink;

        return $this;
    }

    public function blinkRate(int $ms): self
    {
        $this->blinkRate = $ms;

        return $this;
    }

    /**
     * @param array<string> $history
     */
    public function history(array $history): self
    {
        $this->history = $history;

        return $this;
    }

    public function historyEnabled(bool $enabled = true): self
    {
        $this->historyEnabled = $enabled;

        return $this;
    }

    public function masked(bool $masked = true): self
    {
        $this->masked = $masked;

        return $this;
    }

    public function maskChar(string $char): self
    {
        $this->maskChar = $char;

        return $this;
    }

    public function onSubmit(callable $callback): self
    {
        $this->onSubmit = $callback;

        return $this;
    }

    public function onChange(callable $callback): self
    {
        $this->onChange = $callback;

        return $this;
    }

    public function onCancel(callable $callback): self
    {
        $this->onCancel = $callback;

        return $this;
    }

    public function onFocus(callable $callback): self
    {
        $this->onFocus = $callback;

        return $this;
    }

    public function onBlur(callable $callback): self
    {
        $this->onBlur = $callback;

        return $this;
    }

    public function onKeyPress(callable $callback): self
    {
        $this->onKeyPress = $callback;

        return $this;
    }

    public function useTerminalCursor(bool $use = true): self
    {
        $this->useTerminalCursor = $use;

        return $this;
    }

    public function terminalManager(?TerminalManager $manager): self
    {
        $this->terminalManager = $manager;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$inputValue, $setInputValue] = $hooks->state($this->value);
        [$cursorPos, $setCursorPos] = $hooks->state(mb_strlen($this->value));
        [$historyIndex, $setHistoryIndex] = $hooks->state(-1);
        [$cursorVisible, $setCursorVisible] = $hooks->state(true);

        // Use TerminalManager for native cursor control when focused
        if ($this->useTerminalCursor && $this->terminalManager !== null) {
            if ($this->isFocused) {
                // Set cursor shape based on cursor style
                $shape = match ($this->cursorStyle) {
                    CursorStyle::BLOCK => 'block',
                    CursorStyle::UNDERLINE => 'underline',
                    CursorStyle::BAR, CursorStyle::BEAM => 'bar',
                    CursorStyle::NONE => 'block',
                };
                $this->terminalManager->setCursorShape($shape);
                $this->terminalManager->showCursor();
            } else {
                $this->terminalManager->hideCursor();
            }
        }

        if ($this->isFocused && $this->cursorBlink) {
            $hooks->interval(function () use ($setCursorVisible) {
                $setCursorVisible(fn ($v) => !$v);
            }, $this->blinkRate);
        }

        $hooks->onInput(function ($key, $nativeKey) use (
            $setInputValue,
            $setCursorPos,
            $setHistoryIndex,
        ) {
            if (!$this->isFocused) {
                return;
            }

            if ($this->onKeyPress !== null) {
                ($this->onKeyPress)($key, $nativeKey);
            }

            if ($nativeKey->return) {
                $setInputValue(function ($currentValue) {
                    if ($this->onSubmit !== null) {
                        ($this->onSubmit)($currentValue);
                    }

                    return $currentValue;
                });

                return;
            }

            if ($nativeKey->escape) {
                if ($this->onCancel !== null) {
                    ($this->onCancel)();
                }

                return;
            }

            if ($nativeKey->leftArrow) {
                $setCursorPos(fn ($pos) => max(0, $pos - 1));

                return;
            }

            if ($nativeKey->rightArrow) {
                $setInputValue(function ($currentValue) use ($setCursorPos) {
                    $setCursorPos(fn ($pos) => min(mb_strlen($currentValue), $pos + 1));

                    return $currentValue;
                });

                return;
            }

            if ($key === 'home' || ($nativeKey->ctrl && $key === 'a')) {
                $setCursorPos(0);

                return;
            }

            if ($key === 'end' || ($nativeKey->ctrl && $key === 'e')) {
                $setInputValue(function ($currentValue) use ($setCursorPos) {
                    $setCursorPos(mb_strlen($currentValue));

                    return $currentValue;
                });

                return;
            }

            if ($nativeKey->upArrow && $this->historyEnabled && !empty($this->history)) {
                $setHistoryIndex(function ($currentHistoryIndex) use ($setInputValue, $setCursorPos) {
                    $newIndex = min(count($this->history) - 1, $currentHistoryIndex + 1);
                    $historyValue = $this->history[count($this->history) - 1 - $newIndex] ?? '';
                    $setInputValue($historyValue);
                    $setCursorPos(mb_strlen($historyValue));

                    return $newIndex;
                });

                return;
            }

            if ($nativeKey->downArrow && $this->historyEnabled) {
                $setHistoryIndex(function ($currentHistoryIndex) use ($setInputValue, $setCursorPos) {
                    if ($currentHistoryIndex <= 0) {
                        $setInputValue('');
                        $setCursorPos(0);

                        return -1;
                    }

                    $newIndex = $currentHistoryIndex - 1;
                    $historyValue = $this->history[count($this->history) - 1 - $newIndex] ?? '';
                    $setInputValue($historyValue);
                    $setCursorPos(mb_strlen($historyValue));

                    return $newIndex;
                });

                return;
            }

            if ($nativeKey->backspace) {
                $setCursorPos(function ($currentPos) use ($setInputValue) {
                    if ($currentPos > 0) {
                        $setInputValue(function ($currentValue) use ($currentPos) {
                            $newValue = mb_substr($currentValue, 0, $currentPos - 1) . mb_substr($currentValue, $currentPos);

                            if ($this->onChange !== null) {
                                ($this->onChange)($newValue);
                            }

                            return $newValue;
                        });

                        return $currentPos - 1;
                    }

                    return $currentPos;
                });

                return;
            }

            if ($nativeKey->delete) {
                $setCursorPos(function ($currentPos) use ($setInputValue) {
                    $setInputValue(function ($currentValue) use ($currentPos) {
                        $len = mb_strlen($currentValue);
                        if ($currentPos < $len) {
                            $newValue = mb_substr($currentValue, 0, $currentPos) . mb_substr($currentValue, $currentPos + 1);

                            if ($this->onChange !== null) {
                                ($this->onChange)($newValue);
                            }

                            return $newValue;
                        }

                        return $currentValue;
                    });

                    return $currentPos;
                });

                return;
            }

            if (mb_strlen($key) === 1 && !$nativeKey->ctrl && !$nativeKey->meta) {
                $setCursorPos(function ($currentPos) use ($key, $setInputValue) {
                    $setInputValue(function ($currentValue) use ($currentPos, $key) {
                        $newValue = mb_substr($currentValue, 0, $currentPos) . $key . mb_substr($currentValue, $currentPos);

                        if ($this->onChange !== null) {
                            ($this->onChange)($newValue);
                        }

                        return $newValue;
                    });

                    return $currentPos + 1;
                });
            }
        });

        return $this->renderInput($inputValue, $cursorPos, $cursorVisible);
    }

    private function renderInput(string $value, int $cursorPos, bool $cursorVisible): mixed
    {
        $elements = [];

        $elements[] = new Text($this->prompt);

        if ($value === '' && $this->placeholder !== '') {
            $elements[] = new Text($this->placeholder)->dim();
            $elements[] = $this->renderCursor(' ', $cursorVisible);
        } else {
            $displayValue = $this->masked
                ? str_repeat($this->maskChar, mb_strlen($value))
                : $value;

            $beforeCursor = mb_substr($displayValue, 0, $cursorPos);
            $atCursor = mb_substr($displayValue, $cursorPos, 1);
            $afterCursor = mb_substr($displayValue, $cursorPos + 1);

            if ($beforeCursor !== '') {
                $elements[] = new Text($beforeCursor);
            }

            $cursorChar = $atCursor !== '' ? $atCursor : ' ';
            $elements[] = $this->renderCursor($cursorChar, $cursorVisible);

            if ($afterCursor !== '') {
                $elements[] = new Text($afterCursor);
            }
        }

        $hint = $this->hint ?: $this->hintStreaming;
        if ($hint !== '') {
            $elements[] = Spacer::create();
            $elements[] = new Text($hint)->dim();
        }

        $box = new BoxRow($elements);

        if ($this->isFocused) {
            $box = $box->showCursor(true);
        }

        return $box;
    }

    private function renderCursor(string $char, bool $visible): mixed
    {
        if (!$this->isFocused || !$visible) {
            return new Text($char);
        }

        if ($this->cursorChar !== null) {
            return new Text($this->cursorChar);
        }

        return match ($this->cursorStyle) {
            CursorStyle::BLOCK => new Text($char)->inverse(),
            CursorStyle::UNDERLINE => new Text($char)->underline(),
            CursorStyle::BAR, CursorStyle::BEAM => new Text($this->cursorStyle->character() . $char),
            CursorStyle::NONE => new Text($char),
        };
    }
}
