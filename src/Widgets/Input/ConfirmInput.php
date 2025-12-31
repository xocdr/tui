<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Input;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class ConfirmInput extends Widget
{
    private string $question = '';

    private ?string $description = null;

    private bool $default = false;

    private string $variant = 'default';

    private string $yesKey = 'y';

    private string $noKey = 'n';

    /** @var callable|null */
    private $onConfirm = null;

    private function __construct(string $question = '')
    {
        $this->question = $question;
    }

    public static function create(string $question): self
    {
        return new self($question);
    }

    public function question(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function defaultYes(): self
    {
        $this->default = true;

        return $this;
    }

    public function defaultNo(): self
    {
        $this->default = false;

        return $this;
    }

    public function default(bool $value): self
    {
        $this->default = $value;

        return $this;
    }

    public function variant(string $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * @throws \InvalidArgumentException If key is not a single character or conflicts with noKey
     */
    public function yesKey(string $key): self
    {
        $key = strtolower($key);
        if (mb_strlen($key) !== 1) {
            throw new \InvalidArgumentException('yesKey must be a single character');
        }
        if ($key === $this->noKey) {
            throw new \InvalidArgumentException('yesKey and noKey must be different');
        }
        $this->yesKey = $key;

        return $this;
    }

    /**
     * @throws \InvalidArgumentException If key is not a single character or conflicts with yesKey
     */
    public function noKey(string $key): self
    {
        $key = strtolower($key);
        if (mb_strlen($key) !== 1) {
            throw new \InvalidArgumentException('noKey must be a single character');
        }
        if ($key === $this->yesKey) {
            throw new \InvalidArgumentException('yesKey and noKey must be different');
        }
        $this->noKey = $key;

        return $this;
    }

    public function onConfirm(callable $callback): self
    {
        $this->onConfirm = $callback;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        $hooks->onInput(function ($key, $nativeKey) {
            $lowerKey = strtolower($key);

            if ($lowerKey === $this->yesKey) {
                if ($this->onConfirm !== null) {
                    ($this->onConfirm)(true);
                }

                return;
            }

            if ($lowerKey === $this->noKey) {
                if ($this->onConfirm !== null) {
                    ($this->onConfirm)(false);
                }

                return;
            }

            if ($nativeKey->return) {
                if ($this->onConfirm !== null) {
                    ($this->onConfirm)($this->default);
                }

                return;
            }

            if ($nativeKey->escape) {
                if ($this->onConfirm !== null) {
                    ($this->onConfirm)(false);
                }
            }
        });

        $elements = [];

        $questionParts = [];

        if ($this->variant === 'danger') {
            $questionParts[] = new Text('⚠️ ')->color('yellow');
        }

        $questionParts[] = new Text($this->question . ' ');

        $hint = $this->default
            ? '(' . strtoupper($this->yesKey) . '/' . $this->noKey . ')'
            : '(' . $this->yesKey . '/' . strtoupper($this->noKey) . ')';

        $questionParts[] = new Text($hint)->dim();

        $elements[] = new BoxRow($questionParts);

        if ($this->description !== null) {
            $elements[] = new Text($this->description)->dim();
        }

        return new BoxColumn($elements);
    }
}
