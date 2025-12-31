<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Support\Constants;
use Xocdr\Tui\Widgets\Support\IconPresets;
use Xocdr\Tui\Widgets\Widget;

class LoadingState extends Widget
{
    private const STATES = [
        'loading' => ['icon' => 'spinner', 'color' => 'cyan'],
        'success' => ['icon' => '✓', 'color' => 'green'],
        'error' => ['icon' => '✗', 'color' => 'red'],
        'idle' => ['icon' => '○', 'color' => 'gray'],
        'pending' => ['icon' => '◌', 'color' => 'yellow'],
    ];

    private string $state = 'loading';

    private string $message = 'Loading...';

    private ?string $successMessage = null;

    private ?string $errorMessage = null;

    private string $spinnerType = 'dots';

    private bool $showState = true;

    private mixed $children = null;

    private mixed $loadingContent = null;

    private mixed $successContent = null;

    private mixed $errorContent = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public static function loading(string $message = 'Loading...'): self
    {
        return (new self())->state('loading')->message($message);
    }

    public static function success(string $message = 'Success!'): self
    {
        return (new self())->state('success')->message($message);
    }

    public static function error(string $message = 'Error'): self
    {
        return (new self())->state('error')->message($message);
    }

    public function state(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function successMessage(string $message): self
    {
        $this->successMessage = $message;

        return $this;
    }

    public function errorMessage(string $message): self
    {
        $this->errorMessage = $message;

        return $this;
    }

    public function spinnerType(string $type): self
    {
        $this->spinnerType = $type;

        return $this;
    }

    public function showState(bool $show = true): self
    {
        $this->showState = $show;

        return $this;
    }

    public function children(mixed $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function loadingContent(mixed $content): self
    {
        $this->loadingContent = $content;

        return $this;
    }

    public function successContent(mixed $content): self
    {
        $this->successContent = $content;

        return $this;
    }

    public function errorContent(mixed $content): self
    {
        $this->errorContent = $content;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$spinnerFrame, $setSpinnerFrame] = $hooks->state(0);

        if ($this->state === 'loading') {
            $hooks->interval(function () use ($setSpinnerFrame) {
                // @phpstan-ignore argument.type (state setter accepts any int, not just initial value)
                $setSpinnerFrame(fn ($f) => ($f + 1) % Constants::SPINNER_FRAME_COUNT);
            }, Constants::DEFAULT_SPINNER_INTERVAL_MS);
        }

        $stateConfig = self::STATES[$this->state] ?? self::STATES['loading'];

        $message = match ($this->state) {
            'success' => $this->successMessage ?? $this->message,
            'error' => $this->errorMessage ?? $this->message,
            default => $this->message,
        };

        $stateContent = match ($this->state) {
            'loading' => $this->loadingContent,
            'success' => $this->successContent,
            'error' => $this->errorContent,
            default => null,
        };

        $parts = [];

        if ($this->showState) {
            if ($this->state === 'loading') {
                $frames = IconPresets::getSpinner($this->spinnerType);
                $frame = $frames[$spinnerFrame % count($frames)];
                $iconText = Text::create($frame)->color($stateConfig['color']);
            } else {
                $iconText = Text::create($stateConfig['icon'])->color($stateConfig['color']);
            }

            $parts[] = $iconText;
            $parts[] = Text::create(' ');
        }

        $messageText = Text::create($message);
        if ($this->state === 'error') {
            $messageText = $messageText->color('red');
        } elseif ($this->state === 'success') {
            $messageText = $messageText->color('green');
        }

        $parts[] = $messageText;

        $header = Box::row($parts);

        $elements = [$header];

        if ($stateContent !== null) {
            $elements[] = Text::create('');
            $elements[] = is_string($stateContent) ? Text::create($stateContent) : $stateContent;
        } elseif ($this->children !== null && $this->state === 'success') {
            $elements[] = Text::create('');
            $elements[] = is_string($this->children) ? Text::create($this->children) : $this->children;
        }

        return Box::column($elements);
    }
}
